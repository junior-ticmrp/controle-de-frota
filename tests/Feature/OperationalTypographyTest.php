<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalTypographyTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_layout_enforces_readable_text_minimums_for_fields_tables_and_actions(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'typography-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);

        $this->actingAs($actor, 'ldap')->get('/abastecimentos')
            ->assertOk()
            ->assertSee('Tipografia operacional com mínimo de leitura', false)
            ->assertSee('.surface input,.surface select,.surface textarea{font-size:16px', false)
            ->assertSee('.surface table{font-size:15px', false)
            ->assertSee('.surface small{font-size:14px!important', false)
            ->assertSee('.surface table a,.surface table button,.surface form button,.surface form a{font-size:15px', false);
    }
}
