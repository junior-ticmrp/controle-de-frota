<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmrpPlateInlineRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_plate_preview_keeps_explicit_cmrp_markup_in_request_maintenance_and_reports(): void
    {
        $actor = User::create(['name' => 'Supervisor', 'username' => 'inline-plate-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'supervisor', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Etanol', 'abbreviation' => 'ETA', 'active' => true]);
        Vehicle::create(['plate' => 'INL1C26', 'model' => 'Veículo de renderização', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);

        $this->actingAs($actor, 'ldap')->get('/requisicoes/nova')->assertOk()->assertSee('id="vehicle-plate-title"', false)->assertSee('CMRP');
        $this->actingAs($actor, 'ldap')->get('/manutencoes/nova')->assertOk()->assertSee('id="maintenance-plate-title"', false)->assertSee('CMRP');
        $this->actingAs($actor, 'ldap')->get('/relatorios')->assertOk()->assertSee('id="report-plate-title"', false)->assertSee('CMRP');
    }
}
