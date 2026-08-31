<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class ManualTerminologyTest extends TestCase { use RefreshDatabase;
 public function test_manual_uses_plain_network_username_label(): void { $actor=User::create(['name'=>'Supervisor','username'=>'manual-termo'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]); $this->actingAs($actor,'ldap')->get('/manual-operacao')->assertOk()->assertSee('USUÁRIO DE REDE')->assertDontSee('sAMAccountName'); }
}
