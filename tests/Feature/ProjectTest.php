<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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
        return Project::factory()->for($user)->create();
    }

    public function test_index_lista_projetos_com_paginacao()
    {
        Project::factory()->count(15)->for($this->membro)->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson('/api/projects?per_page=5');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['data', 'current_page', 'per_page', 'total']]);

        $this->assertSame(5, $response->json('data.per_page'));
    }

    public function test_index_aceita_filtro_por_status()
    {
        Project::factory()->count(3)->for($this->membro)->state(['status' => 'active'])->create();
        Project::factory()->count(2)->for($this->membro)->state(['status' => 'draft'])->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson('/api/projects?status=active');

        $response->assertOk();
        foreach ($response->json('data.data') as $item) {
            $this->assertSame('active', $item['status']);
        }
    }

    public function test_index_rejeita_status_invalido()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson('/api/projects?status=invalido');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_show_retorna_projeto_proprio()
    {
        $projeto = $this->criarProjeto($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson("/api/projects/{$projeto->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $projeto->id);
    }

    public function test_show_recusa_projeto_de_outro_usuario()
    {
        $outro = User::factory()->create();
        $projeto = $this->criarProjeto($outro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson("/api/projects/{$projeto->id}");

        $response->assertStatus(403);
    }

    public function test_show_404_para_id_inexistente()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson('/api/projects/99999');

        $response->assertStatus(404);
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

    public function test_store_exige_campos_obrigatorios()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'budget', 'status']);
    }

    public function test_store_recusa_budget_negativo()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson('/api/projects', $this->dadosProjeto(['budget' => -10]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['budget']);
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

    public function test_update_valida_payload()
    {
        $projeto = $this->criarProjeto($this->membro);

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/projects/{$projeto->id}", ['title' => '']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
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

    public function test_stats_usa_cache_e_e_invalidado_apos_escrita()
    {
        $projeto = $this->criarProjeto($this->membro);

        $this->actingAs($this->membro, 'sanctum')
            ->getJson("/api/projects/{$projeto->id}/stats")
            ->assertOk();

        $this->assertTrue(Cache::has("project:{$projeto->id}:stats"));

        Task::factory()->for($projeto)->for($this->membro)->create();

        $this->assertFalse(Cache::has("project:{$projeto->id}:stats"));
    }
}
