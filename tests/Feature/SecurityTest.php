<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_headers_de_seguranca_estao_presentes_na_resposta()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_idor_em_board_de_outro_projeto_retorna_404()
    {
        $owner = User::factory()->create();
        $outroProjeto = Project::factory()->for($owner)->create();
        $board = Board::factory()->for($outroProjeto)->for($owner)->create();

        $projetoErrado = Project::factory()->for($owner)->create();

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson("/api/projects/{$projetoErrado->id}/boards/{$board->id}");

        $response->assertStatus(404);
    }

    public function test_idor_em_task_de_outro_projeto_retorna_404()
    {
        $owner = User::factory()->create();
        $projetoA = Project::factory()->for($owner)->create();
        $projetoB = Project::factory()->for($owner)->create();
        $task = Task::factory()->for($projetoA)->for($owner)->create();

        $response = $this->actingAs($owner, 'sanctum')
            ->getJson("/api/projects/{$projetoB->id}/tasks/{$task->id}");

        $response->assertStatus(404);
    }

    public function test_mass_assignment_nao_aceita_campo_extra_no_store_de_projeto()
    {
        $user = User::factory()->create();
        $outroUser = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/projects', [
                'title' => 'Tentativa',
                'description' => 'qualquer',
                'budget' => 1000,
                'status' => 'active',
                'user_id' => $outroUser->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
        ]);
    }

    public function test_token_sanctum_sem_ability_correta_nao_perde_acesso_mas_sessao_e_isolada()
    {
        $owner = User::factory()->create();
        $project = Project::factory()->for($owner)->create();

        $outro = User::factory()->create();
        Sanctum::actingAs($outro, ['*']);

        $response = $this->putJson("/api/projects/{$project->id}", [
            'title' => 'tentativa',
            'budget' => 1,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_login_e_throttled()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'errado',
            ])->assertStatus(401);
        }

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'errado',
        ])->assertStatus(429);
    }
}
