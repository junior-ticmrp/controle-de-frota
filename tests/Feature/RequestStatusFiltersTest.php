<?php
namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestStatusFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_status_filters_show_drafts_cancelled_and_completed_records(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'request-status-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Gasolina', 'abbreviation' => 'GAS', 'active' => true]);
        $vehicle = Vehicle::create(['plate' => 'STS1C26', 'model' => 'Veículo de status', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);
        foreach (['draft' => 9101, 'canceled' => 9102, 'fulfilled' => 9103, 'submitted' => 9104] as $status => $number) {
            FuelRequest::create(['request_number' => $number, 'requested_at' => now(), 'vehicle_id' => $vehicle->id, 'fuel_type_id' => $fuel->id, 'odometer' => 100, 'status' => $status, 'created_by_user_id' => $actor->id]);
        }

        $this->actingAs($actor, 'ldap')->get('/requisicoes?request_status=draft')
            ->assertOk()->assertSee('#9101')->assertDontSee('#9102')->assertDontSee('#9103')->assertSee('Rascunhos')->assertSee('name="request_status"', false);
        $this->actingAs($actor, 'ldap')->get('/requisicoes?request_status=canceled')
            ->assertOk()->assertSee('#9102')->assertDontSee('#9101')->assertDontSee('#9103')->assertSee('Canceladas');
        $this->actingAs($actor, 'ldap')->get('/requisicoes?request_status=fulfilled')
            ->assertOk()->assertSee('#9103')->assertDontSee('#9101')->assertDontSee('#9102')->assertSee('Concluídas');
        $this->actingAs($actor, 'ldap')->get('/requisicoes?request_status=invalid')
            ->assertOk()->assertSee('#9101')->assertSee('#9102')->assertSee('#9103')->assertSee('#9104');
    }
}
