<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePlateSelectorPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_native_vehicle_selectors_expose_plate_previews_for_maintenance_and_reports(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'selector-plate-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Etanol', 'abbreviation' => 'ETA', 'active' => true]);
        Vehicle::create(['plate' => 'SEL1C26', 'model' => 'Veículo de seletor', 'fuel_type_id' => $fuel->id, 'current_odometer' => 500, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);

        $this->actingAs($actor, 'ldap')->get('/manutencoes/nova')
            ->assertOk()->assertSee('id="maintenance-plate-header"', false)->assertSee('data-plate="SEL1C26"', false)->assertSee('syncMaintenancePlate()', false);
        $this->actingAs($actor, 'ldap')->get('/relatorios')
            ->assertOk()->assertSee('id="report-plate-preview"', false)->assertSee('data-plate="SEL1C26"', false)->assertSee('syncReportPlate()', false);
    }
}
