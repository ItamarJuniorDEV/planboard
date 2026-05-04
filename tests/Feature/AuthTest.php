<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('login');
    }

    public function test_usuario_pode_fazer_login()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'data' => ['id', 'name', 'email']]);
    }

    public function test_credenciais_invalidas_retornam_401()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha_errada',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_com_email_inexistente_retorna_mesma_mensagem_de_senha_errada()
    {
        $user = User::factory()->create();

        $respostaInexistente = $this->postJson('/api/login', [
            'email' => 'nao_existe@example.com',
            'password' => 'qualquer',
        ]);

        $respostaSenhaErrada = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'senha_errada',
        ]);

        $this->assertSame(401, $respostaInexistente->status());
        $this->assertSame(401, $respostaSenhaErrada->status());
        $this->assertSame(
            $respostaInexistente->json('message'),
            $respostaSenhaErrada->json('message'),
        );
    }

    public function test_login_exige_campos_obrigatorios()
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_recusa_email_invalido()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'sem-arroba',
            'password' => 'qualquer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_rota_protegida_sem_token_retorna_401()
    {
        $response = $this->getJson('/api/projects');

        $response->assertStatus(401);
    }

    public function test_login_e_throttled_apos_cinco_tentativas()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => $user->email,
                'password' => 'errado',
            ])->assertStatus(401);
        }

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'errado',
        ]);

        $response->assertStatus(429);
    }

    public function test_logout_invalida_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertOk();
    }

    public function test_endpoint_user_retorna_usuario_autenticado()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}
