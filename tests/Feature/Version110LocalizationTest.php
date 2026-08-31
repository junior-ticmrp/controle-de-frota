<?php
namespace Tests\Feature;
use App\Models\FuelType; use App\Models\User; use App\Models\Vehicle; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class Version110LocalizationTest extends TestCase { use RefreshDatabase;
 private function user(): User { return User::create(['name'=>'Supervisor','username'=>'locale'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]); }
 private function vehicle(): Vehicle { $fuel=FuelType::create(['name'=>'Gasolina','abbreviation'=>'GAS','active'=>true]);return Vehicle::create(['plate'=>'LOC1101','model'=>'Teste','fuel_type_id'=>$fuel->id,'current_odometer'=>0,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]); }
 public function test_layout_exposes_keyboard_skip_link_with_a_focusable_main_target(): void { $user=$this->user();$this->actingAs($user,'ldap')->get('/')->assertOk()->assertSee('class="skip-link"',false)->assertSee('href="#main-content"',false)->assertSee('id="main-content" tabindex="-1"',false)->assertSee('.skip-link:focus-visible',false); }
 public function test_request_validation_is_rendered_in_brazilian_portuguese(): void { $user=$this->user();$vehicle=$this->vehicle();$this->actingAs($user,'ldap')->from('/requisicoes/nova')->post('/requisicoes',['requested_at'=>now()->format('Y-m-d H:i:s'),'vehicle_id'=>$vehicle->id,'fuel_type_id'=>$vehicle->fuel_type_id])->assertRedirect('/requisicoes/nova');$this->actingAs($user,'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('O campo hodômetro é obrigatório.')->assertDontSee('The odometer field is required.'); }
}
