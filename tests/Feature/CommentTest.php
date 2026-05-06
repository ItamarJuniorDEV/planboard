<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
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

    public function test_index_lista_comentarios()
    {
        Comment::factory()->count(3)->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments");

        $response->assertOk();
    }

    public function test_store_cria_comentario()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments",
                ['content' => 'Acompanhamento da entrega', 'author' => 'Itamar'],
            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'content' => 'Acompanhamento da entrega',
        ]);
    }

    public function test_store_exige_campos()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments",
                [],
            );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['content', 'author']);
    }

    public function test_update_recusa_dono_diferente()
    {
        $comment = Comment::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}",
                ['content' => 'mudei', 'author' => 'fake'],
            );

        $response->assertStatus(403);
    }

    public function test_destroy_owner_remove_comentario()
    {
        $comment = Comment::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$comment->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_bulk_delete_remove_comentarios()
    {
        $c1 = Comment::factory()->for($this->task)->for($this->owner)->create();
        $c2 = Comment::factory()->for($this->task)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/tasks/{$this->task->id}/comments/bulk-delete",
                ['comment_ids' => [$c1->id, $c2->id, 99999]],
            );

        $response->assertOk();
        $this->assertSame(2, $response->json('deleted'));
        $this->assertSame([99999], $response->json('not_found'));
    }
}
