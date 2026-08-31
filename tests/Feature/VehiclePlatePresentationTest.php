<?php
namespace Tests\Feature;
use App\Models\FuelType; use App\Models\User; use App\Models\Vehicle; use Illuminate\Foundation\Testing\RefreshDatabase; use Tests\TestCase;
class VehiclePlatePresentationTest extends TestCase { use RefreshDatabase;
 public function test_request_header_exposes_accessible_cmrp_plate_presentation(): void { $actor=User::create(['name'=>'Supervisor','username'=>'plate'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]);$fuel=FuelType::create(['name'=>'Etanol','abbreviation'=>'ETA','active'=>true]);Vehicle::create(['plate'=>'PLA1C26','model'=>'Veículo placa','fuel_type_id'=>$fuel->id,'current_odometer'=>1,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]);$this->actingAs($actor,'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('id="vehicle-plate-header"',false)->assertSee('id="vehicle-plate-title"',false)->assertSee('CMRP'); }
}
