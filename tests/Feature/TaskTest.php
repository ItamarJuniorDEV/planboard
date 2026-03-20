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

    private Project $projeto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->membro = User::factory()->create();
        $this->admin = User::factory()->admin()->create();

        $this->projeto = Project::create([
            'user_id' => $this->membro->id,
            'title' => 'Projeto Principal',
            'budget' => 10000,
            'status' => 'active',
        ]);
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

    private function criarTarefa(User $user): Task
    {
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks", $this->dadosTarefa());

        return Task::find($response->json('data.id'));
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

    public function test_pode_atualizar_propria_tarefa()
    {
        $tarefa = $this->criarTarefa($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/projects/{$this->projeto->id}/tasks/{$tarefa->id}", $this->dadosTarefa(['title' => 'Atualizada']));

        $response->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $tarefa->id, 'title' => 'Atualizada']);
    }

    public function test_nao_pode_atualizar_tarefa_de_outro_usuario()
    {
        $outro = User::factory()->create();
        $tarefa = $this->criarTarefa($outro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/projects/{$this->projeto->id}/tasks/{$tarefa->id}", $this->dadosTarefa());

        $response->assertStatus(403);
    }

    public function test_pode_deletar_propria_tarefa()
    {
        $tarefa = $this->criarTarefa($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->deleteJson("/api/projects/{$this->projeto->id}/tasks/{$tarefa->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $tarefa->id]);
    }

    public function test_bulk_delete_remove_tarefas_em_lote()
    {
        $tarefa1 = $this->criarTarefa($this->membro);
        $tarefa2 = $this->criarTarefa($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson("/api/projects/{$this->projeto->id}/tasks/bulk-delete", [
                'task_ids' => [$tarefa1->id, $tarefa2->id],
            ]);

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $tarefa1->id]);
    }
}
