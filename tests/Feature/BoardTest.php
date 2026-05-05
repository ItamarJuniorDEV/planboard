<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BoardTest extends TestCase
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

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Quadro Principal',
            'status' => 'active',
        ], $override);
    }

    public function test_index_lista_boards_do_projeto()
    {
        Board::factory()->count(3)->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/boards");

        $response->assertOk()
            ->assertJsonStructure(['data' => ['data']]);
    }

    public function test_store_cria_board()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/boards", $this->payload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('boards', [
            'project_id' => $this->project->id,
            'name' => 'Quadro Principal',
        ]);
    }

    public function test_store_valida_campos_obrigatorios()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/boards", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_store_rejeita_status_invalido()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/projects/{$this->project->id}/boards", $this->payload(['status' => 'wrong']));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_show_retorna_board_do_projeto()
    {
        $board = Board::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/boards/{$board->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $board->id);
    }

    public function test_show_404_quando_board_pertence_a_outro_projeto()
    {
        $outroProjeto = Project::factory()->for($this->owner)->create();
        $board = Board::factory()->for($outroProjeto)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/boards/{$board->id}");

        $response->assertStatus(404);
    }

    public function test_update_recusa_dono_diferente()
    {
        $board = Board::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson("/api/projects/{$this->project->id}/boards/{$board->id}", $this->payload(['name' => 'Renomeado']));

        $response->assertStatus(403);
    }

    public function test_owner_pode_atualizar_board()
    {
        $board = Board::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->putJson("/api/projects/{$this->project->id}/boards/{$board->id}", $this->payload(['name' => 'Renomeado']));

        $response->assertOk();
        $this->assertDatabaseHas('boards', ['id' => $board->id, 'name' => 'Renomeado']);
    }

    public function test_destroy_recusa_dono_diferente()
    {
        $board = Board::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/boards/{$board->id}");

        $response->assertStatus(403);
    }

    public function test_owner_pode_deletar_board()
    {
        $board = Board::factory()->for($this->project)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/boards/{$board->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('boards', ['id' => $board->id]);
    }
}
