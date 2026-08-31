<?php
namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\Fueling;
use App\Models\Person;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleResponsibility;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicatorsAndResponsibleAttributionTest extends TestCase
{
    use RefreshDatabase;

    private function context(): array
    {
        $actor=User::create(['name'=>'Supervisor','username'=>'indicators-'.uniqid(),'password'=>'SenhaLocal!12345','auth_source'=>'local','role'=>'supervisor','is_active'=>true]);
        DB::table('document_sequences')->updateOrInsert(['name'=>'fuel_request'],['last_value'=>9400]);
        $fuel=FuelType::create(['name'=>'Gasolina','abbreviation'=>'GAS','active'=>true]);
        $council=Person::create(['full_name'=>'Vereador Responsável','role'=>'council_member','active'=>true]);
        $vehicle=Vehicle::create(['plate'=>'IND1C26','model'=>'Veículo indicador','fuel_type_id'=>$fuel->id,'current_odometer'=>100,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]);
        VehicleResponsibility::create(['vehicle_id'=>$vehicle->id,'responsibility_type'=>'council_member','person_id'=>$council->id,'started_at'=>now(),'changed_by_user_id'=>$actor->id]);
        return [$actor,$fuel,$council,$vehicle];
    }

    public function test_indicators_offer_month_semester_year_and_attribute_cost_to_responsible(): void
    {
        [$actor,$fuel,$council,$vehicle]=$this->context();
        $request=FuelRequest::create(['request_number'=>9401,'requested_at'=>now(),'vehicle_id'=>$vehicle->id,'requester_person_id'=>$council->id,'fuel_type_id'=>$fuel->id,'odometer'=>100,'status'=>'fulfilled','created_by_user_id'=>$actor->id]);
        Fueling::create(['fueling_at'=>now(),'request_id'=>$request->id,'vehicle_id'=>$vehicle->id,'fuel_type_id'=>$fuel->id,'odometer'=>120,'liters'=>20,'unit_price'=>5,'total_amount'=>100,'created_by_user_id'=>$actor->id]);
        $this->actingAs($actor,'ldap')->get('/indicadores?period=month&month='.now()->format('Y-m'))->assertOk()->assertSee('Indicadores da frota')->assertSee('Último semestre')->assertSee('Último ano')->assertSee('Vereador Responsável')->assertSee('20,000 L');
    }

    public function test_new_request_uses_council_member_or_sector_and_never_offers_driver_as_operational_field(): void
    {
        [$actor,$fuel,$council,$vehicle]=$this->context();
        $this->actingAs($actor,'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('Solicitante responsável')->assertSee('Setor responsável')->assertDontSee('Informe o motorista real da operação.');
        $response=$this->actingAs($actor,'ldap')->post('/requisicoes',['requested_at'=>now()->format('Y-m-d H:i:s'),'vehicle_id'=>$vehicle->id,'requester_person_id'=>$council->id,'fuel_type_id'=>$fuel->id,'odometer'=>100,'requested_liters'=>10]);
        $response->assertRedirect();$this->assertDatabaseHas('fuel_requests',['vehicle_id'=>$vehicle->id,'requester_person_id'=>$council->id,'responsible_sector'=>null,'status'=>'draft']);
    }

    public function test_single_sector_is_automatically_stored_when_vehicle_has_no_council_member(): void
    {
        [$actor,$fuel,,] = $this->context();
        $vehicle=Vehicle::create(['plate'=>'SET0R26','model'=>'Veículo setorial','fuel_type_id'=>$fuel->id,'current_odometer'=>100,'status'=>'active','maintenance_interval_km'=>10000,'maintenance_interval_days'=>180]);
        VehicleResponsibility::create(['vehicle_id'=>$vehicle->id,'responsibility_type'=>'sector','sector'=>'Transporte','started_at'=>now(),'changed_by_user_id'=>$actor->id]);
        $this->actingAs($actor,'ldap')->post('/requisicoes',['requested_at'=>now()->format('Y-m-d H:i:s'),'vehicle_id'=>$vehicle->id,'fuel_type_id'=>$fuel->id,'odometer'=>100,'requested_liters'=>10])->assertRedirect();
        $this->assertDatabaseHas('fuel_requests',['vehicle_id'=>$vehicle->id,'requester_person_id'=>null,'responsible_sector'=>'Transporte','status'=>'draft']);
    }
}
