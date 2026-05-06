<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Subtask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $outro;

    private Project $project;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->outro = User::factory()->create();
        $this->project = Project::factory()->for($this->owner)->create();
        $this->task = Task::factory()->for($this->project)->for($this->owner)->create();
    }

    public function test_index_lista_subtarefas()
    {
        Subtask::factory()->count(3)->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks");

        $response->assertOk();
    }

    public function test_store_cria_subtarefa()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks",
                ['title' => 'Pesquisar fornecedor', 'done' => false],
            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('subtasks', [
            'task_id' => $this->task->id,
            'title' => 'Pesquisar fornecedor',
        ]);
    }

    public function test_store_exige_titulo()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks",
                ['done' => false],
            );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_recusa_outro_dono()
    {
        $sub = Subtask::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/{$sub->id}",
                ['title' => 'editado', 'done' => true],
            );

        $response->assertStatus(403);
    }

    public function test_owner_pode_atualizar_subtarefa()
    {
        $sub = Subtask::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/{$sub->id}",
                ['title' => 'editado', 'done' => true],
            );

        $response->assertOk();
        $this->assertDatabaseHas('subtasks', ['id' => $sub->id, 'done' => 1]);
    }

    public function test_owner_pode_excluir_subtarefa()
    {
        $sub = Subtask::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/{$sub->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('subtasks', ['id' => $sub->id]);
    }

    public function test_bulk_complete_marca_done()
    {
        $s1 = Subtask::factory()->for($this->task)->for($this->owner)->create(['done' => false]);
        $s2 = Subtask::factory()->for($this->task)->for($this->owner)->create(['done' => false]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/bulk-complete",
                ['subtask_ids' => [$s1->id, $s2->id]],
            );

        $response->assertOk();
        $this->assertSame(2, $response->json('completed'));
        $this->assertTrue($s1->fresh()->done);
    }

    public function test_bulk_delete_remove_subtarefas()
    {
        $s1 = Subtask::factory()->for($this->task)->for($this->owner)->create();
        $s2 = Subtask::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/subtasks/bulk-delete",
                ['subtask_ids' => [$s1->id, $s2->id, 99999]],
            );

        $response->assertOk();
        $this->assertSame(2, $response->json('deleted'));
        $this->assertSame([99999], $response->json('not_found'));
    }
}
