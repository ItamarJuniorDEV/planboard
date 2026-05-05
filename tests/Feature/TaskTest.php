<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $membro;

    private User $admin;

    private User $outro;

    private Project $projeto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->membro = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->outro = User::factory()->create();
        $this->projeto = Project::factory()->for($this->membro)->create();
    }

    private function dadosTarefa(array $override = []): array
    {
        return array_merge([
            'title' => 'Tarefa Teste',
            'description' => 'Descrição da tarefa',
            'priority' => 'medium',
            'status' => 'todo',
        ], $override);
    }

    public function test_index_lista_tarefas_do_projeto()
    {
        Task::factory()->count(3)->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson("/api/projects/{$this->projeto->id}/tasks");

        $response->assertOk();
    }

    public function test_pode_criar_tarefa()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks", $this->dadosTarefa());

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Tarefa Teste',
            'user_id' => $this->membro->id,
        ]);
    }

    public function test_store_exige_campos_obrigatorios()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'priority', 'status']);
    }

    public function test_store_recusa_prioridade_invalida()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks", $this->dadosTarefa(['priority' => 'inexistente']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_show_404_quando_tarefa_pertence_a_outro_projeto()
    {
        $outroProjeto = Project::factory()->for($this->membro)->create();
        $task = Task::factory()->for($outroProjeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson("/api/projects/{$this->projeto->id}/tasks/{$task->id}");

        $response->assertStatus(404);
    }

    public function test_pode_atualizar_propria_tarefa()
    {
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->projeto->id}/tasks/{$task->id}",
                $this->dadosTarefa(['title' => 'Atualizada']),
            );

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Atualizada']);
    }

    public function test_nao_pode_atualizar_tarefa_de_outro_usuario()
    {
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->projeto->id}/tasks/{$task->id}",
                $this->dadosTarefa(),
            );

        $response->assertStatus(403);
    }

    public function test_admin_pode_atualizar_qualquer_tarefa()
    {
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson(
                "/api/projects/{$this->projeto->id}/tasks/{$task->id}",
                $this->dadosTarefa(['title' => 'pelo admin']),
            );

        $response->assertOk();
    }

    public function test_pode_deletar_propria_tarefa()
    {
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->deleteJson("/api/projects/{$this->projeto->id}/tasks/{$task->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_move_to_column_move_a_tarefa()
    {
        $board = Board::factory()->for($this->projeto)->for($this->membro)->create();
        $column = Column::factory()->for($board)->for($this->membro)->create();
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create(['column_id' => $column->id]);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->patchJson("/api/projects/{$this->projeto->id}/boards/{$board->id}/columns/{$column->id}/tasks/{$task->id}/move");

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'column_id' => $column->id]);
    }

    public function test_move_to_column_recusa_quando_tarefa_nao_e_do_dono()
    {
        $board = Board::factory()->for($this->projeto)->for($this->membro)->create();
        $column = Column::factory()->for($board)->for($this->membro)->create();
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create(['column_id' => $column->id]);

        $response = $this->actingAs($this->outro, 'sanctum')
            ->patchJson("/api/projects/{$this->projeto->id}/boards/{$board->id}/columns/{$column->id}/tasks/{$task->id}/move");

        $response->assertStatus(403);
    }

    public function test_bulk_move_atualiza_tarefas_e_reporta_nao_encontradas()
    {
        $board = Board::factory()->for($this->projeto)->for($this->membro)->create();
        $column = Column::factory()->for($board)->for($this->membro)->create();
        $task1 = Task::factory()->for($this->projeto)->for($this->membro)->create();
        $task2 = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->patchJson("/api/projects/{$this->projeto->id}/tasks/bulk-move", [
                'task_ids' => [$task1->id, $task2->id, 99999],
                'column_id' => $column->id,
            ]);

        $response->assertOk();
        $this->assertSame(2, $response->json('moved'));
        $this->assertSame([99999], $response->json('not_found'));
    }

    public function test_bulk_delete_remove_tarefas_em_lote()
    {
        $task1 = Task::factory()->for($this->projeto)->for($this->membro)->create();
        $task2 = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks/bulk-delete", [
                'task_ids' => [$task1->id, $task2->id],
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task1->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task2->id]);
    }

    public function test_bulk_delete_reporta_not_found()
    {
        $task = Task::factory()->for($this->projeto)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks/bulk-delete", [
                'task_ids' => [$task->id, 99999],
            ]);

        $response->assertOk();
        $this->assertSame(1, $response->json('deleted'));
        $this->assertSame([99999], $response->json('not_found'));
    }
}
