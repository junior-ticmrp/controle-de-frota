<?php
namespace Tests\Feature;
use App\Models\User; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class ManualWebTest extends TestCase { use RefreshDatabase;
 private function actor(): User { return User::create(['name'=>'Supervisor','username'=>'manual'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]); }
 public function test_authenticated_user_reads_web_manual_and_embedded_printing_steps(): void { $actor=$this->actor(); $this->actingAs($actor,'ldap')->get('/manual-operacao')->assertOk()->assertSee('Configuração de impressão A4')->assertSee('Configuração de impressora térmica 80 mm'); $this->actingAs($actor,'ldap')->get('/manual-operacao?embed=1')->assertOk()->assertSee('Roll Paper 80 × 297 mm'); }
 public function test_dashboard_exposes_new_tab_manual_link_before_authenticated_status(): void { $actor=$this->actor(); $this->actingAs($actor,'ldap')->get('/')->assertOk()->assertSee('class="manual-link"',false)->assertSee('target="_blank"',false)->assertDontSee('id="manual-dialog"',false)->assertSee('Sessão autenticada'); }
}
