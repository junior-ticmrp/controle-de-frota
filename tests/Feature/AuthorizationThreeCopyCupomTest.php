<?php

namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\Person;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationThreeCopyCupomTest extends TestCase
{
    use RefreshDatabase;

    private function approvedRequest(): array
    {
        $actor = User::create([
            'name' => 'Supervisor',
            'username' => 'auth-cupom-'.uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => 'supervisor',
            'is_active' => true,
        ]);
        $fuel = FuelType::create([
            'name' => 'Gasolina',
            'abbreviation' => 'GAS',
            'active' => true,
        ]);
        $vehicle = Vehicle::create([
            'plate' => 'ABC1D23',
            'model' => 'Teste',
            'fuel_type_id' => $fuel->id,
            'current_odometer' => 1000,
            'status' => 'active',
            'maintenance_interval_km' => 10000,
            'maintenance_interval_days' => 180,
        ]);
        $driver = Person::create([
            'full_name' => 'Motorista',
            'role' => 'driver',
            'active' => true,
        ]);
        $request = FuelRequest::create([
            'request_number' => 321,
            'requested_at' => now(),
            'vehicle_id' => $vehicle->id,
            'driver_person_id' => $driver->id,
            'fuel_type_id' => $fuel->id,
            'odometer' => 1000,
            'status' => 'approved',
            'approved_by_user_id' => $actor->id,
            'approved_at' => now(),
            'authorization_at' => now(),
            'authorization_expires_at' => now()->addDays(3),
            'created_by_user_id' => $actor->id,
        ]);

        return [$actor, $request];
    }

    public function test_request_detail_exposes_a4_and_cupom_authorization_actions(): void
    {
        [$actor, $request] = $this->approvedRequest();

        $this->actingAs($actor, 'ldap')
            ->get("/requisicoes/{$request->id}")
            ->assertOk()
            ->assertSee('Autorização - 3 vias A4')
            ->assertSee('Autorização - 3 vias Cupom')
            ->assertSee('/autorizacao-3-vias-cupom');
    }

    public function test_cupom_authorization_renders_three_80mm_copies_for_epson_t20(): void
    {
        [$actor, $request] = $this->approvedRequest();

        $response = $this->actingAs($actor, 'ldap')
            ->get("/requisicoes/{$request->id}/autorizacao-3-vias-cupom");

        $response->assertOk()
            ->assertSee('Formato térmico 80 mm · Epson T20')
            ->assertSee('size: 80mm 297mm', false)
            ->assertSee('VIA POSTO')
            ->assertSee('VIA MOTORISTA')
            ->assertSee('VIA CMRP')
            ->assertSee('Válido por 3 dias')
            ->assertSee('Responsável administrativo');

        $this->assertSame(3, substr_count($response->getContent(), 'class="copy"'));
    }
}
