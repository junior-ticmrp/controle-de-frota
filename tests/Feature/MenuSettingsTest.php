<?php

namespace Tests\Feature;

use App\Models\MenuVisibility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_technical_administration_can_open_menu_settings(): void
    {
        $admin = $this->user('admin');
        $operator = $this->user('supervisor');

        $this->actingAs($admin, 'ldap')
            ->get('/configuracoes/menu')
            ->assertOk()
            ->assertSee('Configuração do menu')
            ->assertSee('Usuário')
            ->assertSee('Operador');

        $this->actingAs($operator, 'ldap')
            ->get('/configuracoes/menu')
            ->assertForbidden();
    }

    public function test_admin_can_hide_an_item_for_a_profile_without_changing_route_authorization(): void
    {
        $admin = $this->user('admin');
        $this->user('supervisor');
        $user = $this->user('user');

        $settings = [
            'dashboard' => '1',
            'requests' => '1',
            'fuelings' => '1',
            'maintenance' => '1',
            'reports' => '0',
            'indicators' => '1',
        ];

        $this->actingAs($admin, 'ldap')
            ->put('/configuracoes/menu', ['settings' => ['user' => $settings]])
            ->assertRedirect('/configuracoes/menu');

        $this->assertFalse(MenuVisibility::query()->where(['role' => 'user', 'menu_key' => 'reports'])->value('enabled'));

        $this->actingAs($user, 'ldap')
            ->get('/')
            ->assertOk()
            ->assertDontSee('href="'.route('reports.index').'"', false)
            ->assertSee('href="'.route('fuel-requests.index').'"', false);

        $this->actingAs($user, 'ldap')
            ->get('/relatorios')
            ->assertOk();
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => 'Menu '.$role,
            'username' => 'menu-'.$role.'-'.uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
