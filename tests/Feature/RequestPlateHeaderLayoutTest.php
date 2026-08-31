<?php
namespace Tests\Feature;

use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestPlateHeaderLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_plate_is_placed_in_the_header_not_in_the_form_grid(): void
    {
        $actor = User::create(['name' => 'Operador', 'username' => 'request-header-'.uniqid(), 'password' => 'SenhaLocal!12345', 'auth_source' => 'local', 'role' => 'user', 'is_active' => true]);
        $fuel = FuelType::create(['name' => 'Etanol', 'abbreviation' => 'ETA', 'active' => true]);
        Vehicle::create(['plate' => 'REQ1C26', 'model' => 'Veículo de requisição', 'fuel_type_id' => $fuel->id, 'current_odometer' => 100, 'status' => 'active', 'maintenance_interval_km' => 10000, 'maintenance_interval_days' => 180]);

        $this->actingAs($actor, 'ldap')->get('/requisicoes/nova')
            ->assertOk()
            ->assertSeeInOrder(['class="request-heading"', 'id="vehicle-plate-header"', '<section class="surface">'], false)
            ->assertDontSee('id="vehicle-plate-preview"', false)
            ->assertSee('document.getElementById(\'vehicle-plate-header\')', false);
    }
}
