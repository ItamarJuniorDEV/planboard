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
        $this->assertNotEquals('minhasenha', $usuario->password);
    }

    public function test_admin_pode_deletar_usuario()
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/users/{$usuario->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $usuario->id]);
    }
}
