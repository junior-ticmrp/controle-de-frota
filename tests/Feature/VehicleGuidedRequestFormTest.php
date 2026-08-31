<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\Person;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleResponsibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleGuidedRequestFormTest extends TestCase
{
    use RefreshDatabase;

    private function supervisor(): User { return User::create(['name'=>'Supervisor','username'=>'guided'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]); }

    public function test_vehicle_selection_exposes_registered_responsible_guidance_and_reference_price(): void
    {
        $actor=$this->supervisor();$fuel=FuelType::create(['name'=>'Etanol','abbreviation'=>'ETA','active'=>true]);$vehicle=Vehicle::create(['plate'=>'GUI1D26','model'=>'Veículo guiado','fuel_type_id'=>$fuel->id,'current_odometer'=>32100,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]);$council=Person::create(['full_name'=>'Responsável cadastrado','role'=>'council_member','active'=>true]);VehicleResponsibility::create(['vehicle_id'=>$vehicle->id,'responsibility_type'=>'council_member','person_id'=>$council->id,'started_at'=>now()]);DB::table('valorcomb')->insert(['fuel_type_id'=>$fuel->id,'effective_at'=>now()->subMinute(),'valor_bruto'=>6,'desconto'=>0.25,'valorcomb'=>5.75,'source'=>'teste','created_at'=>now()]);
        $this->actingAs($actor,'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('1. Selecione o veículo')->assertSee('vehicle-choice',false)->assertSee('data-fuel-type-id="'.$fuel->id.'"',false)->assertSee('data-requester-id="'.$council->id.'"',false)->assertSee('data-odometer="32100"',false)->assertSee('Solicitante responsável')->assertSee('Preço de referência (R$/L)')->assertSee('id="reference_unit_price"',false)->assertSee('id="vehicle-plate-header"',false)->assertDontSee('Informe o motorista real da operação.');
    }

    public function test_request_form_keeps_liters_and_estimated_amount_editable_without_driver_field(): void
    {
        $actor=$this->supervisor();$fuel=FuelType::create(['name'=>'Gasolina','abbreviation'=>'GAS','active'=>true]);Vehicle::create(['plate'=>'GUI2D26','model'=>'Veículo guiado','fuel_type_id'=>$fuel->id,'current_odometer'=>100,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]);
        $this->actingAs($actor,'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('name="requested_liters"',false)->assertSee('name="estimated_amount"',false)->assertDontSee('name="driver_person_id"',false)->assertSee('Selecione o veículo para carregar combustível, responsável, hodômetro e estimativa.');
    }
}
