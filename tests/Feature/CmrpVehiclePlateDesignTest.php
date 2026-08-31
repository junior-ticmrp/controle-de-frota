<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmrpVehiclePlateDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_vehicle_selection_exposes_the_cmrp_plate_design(): void
    {
        $actor = User::create(['name' => 'Operador', 'username' => 'cmrp-plate-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'user', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Gasolina', 'abbreviation' => 'GAS', 'active' => true]);
        Vehicle::create(['plate' => 'CMR1P26', 'model' => 'Veículo institucional', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);

        $this->actingAs($actor, 'ldap')->get('/requisicoes/nova')
            ->assertOk()
            ->assertSee('Desenho institucional da placa CMRP', false)
            ->assertSee('content:"CMRP"', false)
            ->assertSee('.vehicle-plate-preview .vehicle-plate', false)
            ->assertSee('id="vehicle-plate-header"', false);
    }
}
