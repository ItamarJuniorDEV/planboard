<?php

namespace Tests\Feature;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MilestoneTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $outro;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->outro = User::factory()->create();
        $this->project = Project::factory()->for($this->owner)->create();
    }

    public function test_index_lista_milestones()
    {
        Milestone::factory()->count(3)->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/milestones");

        $response->assertOk();
    }

    public function test_index_filtra_por_busca()
    {
        Milestone::factory()->for($this->project)->for($this->owner)->create(['title' => 'Entrega final']);
        Milestone::factory()->for($this->project)->for($this->owner)->create(['title' => 'Sprint 1']);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/milestones?search=Entrega");

        $response->assertOk();
        $titles = array_column($response->json('data.data'), 'title');
        $this->assertContains('Entrega final', $titles);
        $this->assertNotContains('Sprint 1', $titles);
    }

    public function test_store_cria_marco()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/milestones", [
                'title' => 'Lançamento',
                'due_date' => '2026-08-15',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('milestones', [
            'project_id' => $this->project->id,
            'title' => 'Lançamento',
        ]);
    }

    public function test_store_exige_titulo()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/milestones", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_update_recusa_outro_dono()
    {
        $milestone = Milestone::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->project->id}/milestones/{$milestone->id}",
                ['title' => 'editado', 'due_date' => '2026-12-01'],
            );

        $response->assertStatus(403);
    }

    public function test_destroy_owner_remove_milestone()
    {
        $milestone = Milestone::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/milestones/{$milestone->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('milestones', ['id' => $milestone->id]);
    }
}
