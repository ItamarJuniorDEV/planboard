<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_pode_listar_usuarios()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/users?per_page=5');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['data', 'current_page', 'per_page']]);
    }

    public function test_member_pode_listar_usuarios()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->getJson('/api/users');

        $response->assertOk();
    }

    public function test_admin_pode_criar_usuario()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Novo Usuário',
                'email' => 'novo@example.com',
                'password' => 'senha1234',
                'role' => 'member',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'novo@example.com']);
    }

    public function test_store_valida_email_unico()
    {
        $existente = User::factory()->create(['email' => 'duplicado@example.com']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Outro',
                'email' => 'duplicado@example.com',
                'password' => 'senha1234',
                'role' => 'member',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_recusa_senha_curta()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'X',
                'email' => 'x@example.com',
                'password' => 'abc',
                'role' => 'member',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_member_nao_pode_criar_usuario()
    {
        $response = $this->actingAs($this->membro, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Tentativa',
                'email' => 'tentativa@example.com',
                'password' => 'senha1234',
            ]);

        $response->assertStatus(403);
    }

    public function test_member_nao_pode_atualizar_usuario()
    {
        $alvo = User::factory()->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->putJson("/api/users/{$alvo->id}", [
                'name' => 'Tentativa',
                'email' => $alvo->email,
            ]);

        $response->assertStatus(403);
    }

    public function test_admin_pode_atualizar_usuario()
    {
        $alvo = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/users/{$alvo->id}", [
                'name' => 'Editado pelo admin',
                'email' => $alvo->email,
                'role' => 'member',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $alvo->id, 'name' => 'Editado pelo admin']);
    }

    public function test_update_aceita_email_atual_do_usuario()
    {
        $alvo = User::factory()->create(['email' => 'mesmo@example.com']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/users/{$alvo->id}", [
                'name' => 'Editado',
                'email' => 'mesmo@example.com',
            ]);

        $response->assertOk();
    }

    public function test_senha_e_armazenada_com_hash()
    {
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/users', [
                'name' => 'Usuário Hash',
                'email' => 'hash@example.com',
                'password' => 'minhasenha',
                'role' => 'member',
            ]);

        $usuario = User::where('email', 'hash@example.com')->first();
        $this->assertNotSame('minhasenha', $usuario->password);
    }

    public function test_admin_pode_deletar_usuario()
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/users/{$usuario->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }

    public function test_member_nao_pode_deletar_usuario()
    {
        $alvo = User::factory()->create();

        $response = $this->actingAs($this->membro, 'sanctum')
            ->deleteJson("/api/users/{$alvo->id}");

        $response->assertStatus(403);
    }

    public function test_admin_nao_pode_deletar_a_si_mesmo()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }
}
