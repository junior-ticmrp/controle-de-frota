<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenancePlateHeaderLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_maintenance_plate_is_placed_in_the_header_not_in_the_form_grid(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'header-plate-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Gasolina', 'abbreviation' => 'GAS', 'active' => true]);
        Vehicle::create(['plate' => 'HDR1C26', 'model' => 'Veículo de cabeçalho', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);

        $this->actingAs($actor, 'ldap')->get('/manutencoes/nova')
            ->assertOk()
            ->assertSeeInOrder(['class="maintenance-heading"', 'id="maintenance-plate-header"', '<section class="surface">'], false)
            ->assertDontSee('id="maintenance-plate-preview"', false)
            ->assertSee('document.getElementById(\'maintenance-plate-header\')', false);
    }
}
