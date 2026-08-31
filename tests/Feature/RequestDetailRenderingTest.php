<?php
namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestDetailRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_opens_the_detail_of_a_new_draft_without_blade_parse_error(): void
    {
        $actor = User::create([
            'name' => 'Usuário de teste',
            'username' => 'request-detail-'.uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => 'user',
            'is_active' => true,
        ]);
        $fuel = FuelType::create(['name' => 'Etanol', 'abbreviation' => 'ETA', 'active' => true]);
        $vehicle = Vehicle::create([
            'plate' => 'DET1L26',
            'model' => 'Veículo de detalhe',
            'fuel_type_id' => $fuel->id,
            'current_odometer' => 1200,
            'status' => 'active',
            'maintenance_interval_km' => 10000,
            'maintenance_interval_days' => 180,
        ]);
        $request = FuelRequest::create([
            'request_number' => 801,
            'requested_at' => now(),
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuel->id,
            'odometer' => 1210,
            'requested_liters' => 20,
            'estimated_amount' => 115,
            'status' => 'draft',
            'created_by_user_id' => $actor->id,
        ]);

        $this->actingAs($actor, 'ldap')
            ->get("/requisicoes/{$request->id}")
            ->assertOk()
            ->assertSee('Solicitação')
            ->assertSee('Valor estimado')
            ->assertSee('Autorização')
            ->assertSee('Enviar');
    }
}
