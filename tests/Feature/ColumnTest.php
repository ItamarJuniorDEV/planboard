<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColumnTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private User $outro;

    private Project $project;

    private Board $board;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->outro = User::factory()->create();
        $this->project = Project::factory()->for($this->owner)->create();
        $this->board = Board::factory()->for($this->project)->for($this->owner)->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'A fazer',
            'position' => 1,
        ], $override);
    }

    public function test_index_lista_colunas_do_quadro()
    {
        Column::factory()->count(3)->for($this->board)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/boards/{$this->board->id}/columns");

        $response->assertOk();
    }

    public function test_store_cria_coluna()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/boards/{$this->board->id}/columns",
                $this->payload(),
            );

        $response->assertStatus(201);
        $this->assertDatabaseHas('columns', [
            'board_id' => $this->board->id,
            'name' => 'A fazer',
        ]);
    }

    public function test_store_valida_campos_obrigatorios()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson(
                "/api/projects/{$this->project->id}/boards/{$this->board->id}/columns",
                [],
            );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'position']);
    }

    public function test_show_404_para_coluna_de_outro_board()
    {
        $outroBoard = Board::factory()->for($this->project)->for($this->owner)->create();
        $column = Column::factory()->for($outroBoard)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/projects/{$this->project->id}/boards/{$this->board->id}/columns/{$column->id}");

        $response->assertStatus(404);
    }

    public function test_update_403_para_outro_dono()
    {
        $column = Column::factory()->for($this->board)->for($this->owner)->create();

        $response = $this->actingAs($this->outro, 'sanctum')
            ->putJson(
                "/api/projects/{$this->project->id}/boards/{$this->board->id}/columns/{$column->id}",
                $this->payload(['name' => 'Mudei']),
            );

        $response->assertStatus(403);
    }

    public function test_destroy_owner_remove_coluna()
    {
        $column = Column::factory()->for($this->board)->for($this->owner)->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/projects/{$this->project->id}/boards/{$this->board->id}/columns/{$column->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('columns', ['id' => $column->id]);
    }
}
