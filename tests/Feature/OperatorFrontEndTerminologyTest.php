<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorFrontEndTerminologyTest extends TestCase
{
    use RefreshDatabase;

    public function test_technical_supervisor_role_is_presented_as_operator_in_the_authenticated_interface(): void
    {
        $actor = User::create([
            'name' => 'Pessoa de teste',
            'username' => 'operator-label-'.uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => 'supervisor',
            'is_active' => true,
        ]);

        $this->actingAs($actor, 'ldap')->get('/')
            ->assertOk()
            ->assertSee('class="identity-role">Operador</span>', false)
            ->assertDontSee('class="identity-role">Supervisor</span>', false);

        $this->assertSame('supervisor', $actor->fresh()->role);
    }

    public function test_layout_keeps_the_internal_role_key_and_changes_only_its_visible_label(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString("'supervisor'=>'Operador'", $layout);
        $this->assertStringNotContainsString("'supervisor'=>'Supervisor'", $layout);
    }
}
