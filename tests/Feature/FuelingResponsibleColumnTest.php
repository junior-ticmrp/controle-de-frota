<?php
namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\Fueling;
use App\Models\Person;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleResponsibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelingResponsibleColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_fueling_list_shows_request_responsible_before_vehicle_and_falls_back_to_vehicle_responsibility(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'fueling-responsible-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Gasolina', 'abbreviation' => 'GAS', 'active' => true]);
        $requester = Person::create(['full_name' => 'Responsável da requisição', 'role' => 'council_member', 'active' => true]);
        $vehicleResponsible = Person::create(['full_name' => 'Responsável atual do veículo', 'role' => 'council_member', 'active' => true]);
        $vehicle = Vehicle::create(['plate' => 'RSP1C26', 'model' => 'Veículo de responsável', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);
        VehicleResponsibility::create(['vehicle_id' => $vehicle->id, 'responsibility_type' => 'council_member', 'person_id' => $vehicleResponsible->id, 'started_at' => now(), 'changed_by_user_id' => $actor->id]);
        $request = FuelRequest::create(['request_number' => 9201, 'requested_at' => now(), 'vehicle_id' => $vehicle->id, 'requester_person_id' => $requester->id, 'fuel_type_id' => $fuel->id, 'odometer' => 100, 'status' => 'fulfilled', 'created_by_user_id' => $actor->id]);
        Fueling::create(['fueling_at' => now(), 'request_id' => $request->id, 'vehicle_id' => $vehicle->id, 'fuel_type_id' => $fuel->id, 'odometer' => 110, 'liters' => 20, 'unit_price' => 5, 'total_amount' => 100, 'created_by_user_id' => $actor->id]);

        $this->actingAs($actor, 'ldap')->get('/abastecimentos')
            ->assertOk()
            ->assertSeeInOrder(['<th>Responsável</th>', '<th>Veículo</th>', 'Responsável da requisição', 'RSP1C26'], false)
            ->assertDontSee('Responsável atual do veículo');
    }
}
