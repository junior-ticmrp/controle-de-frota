<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemainingSmallTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_refines_navigation_table_helper_and_footer_text_sizes(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'small-text-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);

        $this->actingAs($actor, 'ldap')->get('/requisicoes')
            ->assertOk()
            ->assertSee('Refinamento visual de textos operacionais', false)
            ->assertSee('.nav-item{font-size:15px!important', false)
            ->assertSee('.surface table{font-size:16px!important', false)
            ->assertSee('.surface table th{font-size:15px!important', false)
            ->assertSee('.app-footer{font-size:13px!important', false);
    }
}
