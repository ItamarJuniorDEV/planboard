<?php

namespace Tests\Feature;

use App\Models\Label;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelTest extends TestCase
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

    public function test_index_lista_labels()
    {
        Label::factory()->count(3)->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/labels");

        $response->assertOk();
    }

    public function test_index_filtra_por_nome()
    {
        Label::factory()->for($this->project)->for($this->owner)->create(['name' => 'urgent']);
        Label::factory()->for($this->project)->for($this->owner)->create(['name' => 'documentation']);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/labels?search=urg");

        $response->assertOk();
        $names = array_column($response->json('data.data'), 'name');
        $this->assertContains('urgent', $names);
        $this->assertNotContains('documentation', $names);
    }

    public function test_store_cria_label()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/labels", [
                'name' => 'bug',
                'color' => '#e11d48',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('labels', [
            'project_id' => $this->project->id,
            'name' => 'bug',
        ]);
    }

    public function test_store_exige_campos()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/labels", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'color']);
    }

    public function test_update_recusa_outro_dono()
    {
        $label = Label::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson("/api/projects/{$this->project->id}/labels/{$label->id}", [
                'name' => 'fake',
                'color' => '#000000',
            ]);

        $response->assertStatus(403);
    }

    public function test_destroy_owner_remove_label()
    {
        $label = Label::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/labels/{$label->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('labels', ['id' => $label->id]);
    }
}
