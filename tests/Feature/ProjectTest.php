<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $membro;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->membro = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
    }

    private function dadosProjeto(array $override = []): array
    {
        return array_merge([
            'title' => 'Projeto Teste',
            'description' => 'Descrição do projeto',
            'budget' => 5000.00,
            'status' => 'active',
            'deadline' => '2026-12-31',
        ], $override);
    }

    private function criarProjeto(User $user): Project
    {
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/projects', $this->dadosProjeto());

        return Project::find($response->json('data.id'));
    }

    public function test_pode_criar_projeto()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson('/api/projects', $this->dadosProjeto());

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', [
            'title' => 'Projeto Teste',
            'user_id' => $this->membro->id,
        ]);
    }

    public function test_pode_atualizar_proprio_projeto()
    {
        $projeto = $this->criarProjeto($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/projects/{$projeto->id}", $this->dadosProjeto(['title' => 'Novo título']));

        $response->assertOk();
        $this->assertDatabaseHas('projects', ['id' => $projeto->id, 'title' => 'Novo título']);
    }

    public function test_nao_pode_atualizar_projeto_de_outro_usuario()
    {
        $outro = User::factory()->create();
        $projeto = $this->criarProjeto($outro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/projects/{$projeto->id}", $this->dadosProjeto(['title' => 'Tentativa']));

        $response->assertStatus(403);
    }

    public function test_admin_pode_atualizar_qualquer_projeto()
    {
        $projeto = $this->criarProjeto($this->membro);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/projects/{$projeto->id}", $this->dadosProjeto(['title' => 'Editado pelo admin']));

        $response->assertOk();
    }

    public function test_pode_deletar_proprio_projeto()
    {
        $projeto = $this->criarProjeto($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->deleteJson("/api/projects/{$projeto->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('projects', ['id' => $projeto->id]);
    }

    public function test_nao_pode_deletar_projeto_de_outro_usuario()
    {
        $outro = User::factory()->create();
        $projeto = $this->criarProjeto($outro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->deleteJson("/api/projects/{$projeto->id}");

        $response->assertStatus(403);
    }
}
