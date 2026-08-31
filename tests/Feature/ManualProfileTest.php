<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_sees_only_the_user_manual(): void
    {
        $response = $this->actingAs($this->user('user'), 'ldap')->get('/manual-operacao');

        $response->assertOk()
            ->assertSee('Manual do Usuário')
            ->assertSee('Criar requisição')
            ->assertSee('Perfil exibido:')
            ->assertDontSee('Manual do Operador')
            ->assertDontSee('Manual Técnico');
    }

    public function test_operator_sees_only_the_operator_manual(): void
    {
        $response = $this->actingAs($this->user('supervisor'), 'ldap')->get('/manual-operacao');

        $response->assertOk()
            ->assertSee('Manual do Operador')
            ->assertSee('Analisar e decidir')
            ->assertSee('Autorizações e impressão')
            ->assertDontSee('Manual do Usuário')
            ->assertDontSee('Manual Técnico');
    }

    public function test_technical_administration_sees_only_the_technical_manual(): void
    {
        $response = $this->actingAs($this->user('admin'), 'ldap')->get('/manual-operacao');

        $response->assertOk()
            ->assertSee('Manual Técnico')
            ->assertSee('Configuração do menu')
            ->assertSee('HTTPS e rede interna')
            ->assertDontSee('Manual do Usuário')
            ->assertDontSee('Manual do Operador');
    }

    public function test_embedded_manual_uses_the_same_profile_selection(): void
    {
        $this->actingAs($this->user('supervisor'), 'ldap')
            ->get('/manual-operacao?embed=1')
            ->assertOk()
            ->assertSee('Manual do Operador')
            ->assertSee('Analisar e decidir')
            ->assertDontSee('Manual Técnico');
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => 'Manual '.$role,
            'username' => 'manual-profile-'.$role.'-'.uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
