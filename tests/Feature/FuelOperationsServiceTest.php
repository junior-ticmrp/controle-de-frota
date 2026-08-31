<?php

namespace Tests\Feature;

use App\Models\FuelRequest;
use App\Models\FuelType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\FuelRequestService;
use App\Services\FuelingService;
use App\Services\OperationalPermissionException;
use App\Services\OperationalStateException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelOperationsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creates_and_submits_and_supervisor_approves_and_fulfills(): void
    {
        [$fuelType, $vehicle] = $this->fuelContext(10000);
        $user = $this->user('user');
        $supervisor = $this->user('supervisor');

        $requests = app(FuelRequestService::class);
        $fuelings = app(FuelingService::class);

        $draft = $requests->createDraft($user, [
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'odometer' => 10050,
            'requested_liters' => '25.000',
        ]);

        $this->assertSame(1, $draft->request_number);
        $this->assertSame('draft', $draft->status);

        $submitted = $requests->submit($user, $draft->id);
        $this->assertSame('submitted', $submitted->status);

        $approved = $requests->approve($supervisor, $draft->id);
        $this->assertSame('approved', $approved->status);
        $this->assertSame($supervisor->id, $approved->approved_by_user_id);

        $fueling = $fuelings->fulfill($supervisor, [
            'request_id' => $draft->id,
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'odometer' => 10100,
            'liters' => '20.000',
            'unit_price' => '5.500',
        ]);

        $this->assertSame('110.00', $fueling->total_amount);
        $this->assertDatabaseHas('fuel_requests', ['id' => $draft->id, 'status' => 'fulfilled']);
        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'current_odometer' => 10100]);
        $this->assertDatabaseHas('fuelings', ['id' => $fueling->id, 'request_id' => $draft->id]);
    }

    public function test_only_supervisor_can_approve_reject_or_fulfill(): void
    {
        [$fuelType, $vehicle] = $this->fuelContext(5000);
        $user = $this->user('user');
        $admin = $this->user('admin');
        $supervisor = $this->user('supervisor');
        $requests = app(FuelRequestService::class);

        $request = $requests->createDraft($user, [
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'odometer' => 5010,
        ]);
        $requests->submit($user, $request->id);

        $this->expectException(OperationalPermissionException::class);
        $requests->approve($admin, $request->id);

        $this->assertSame('submitted', $request->fresh()->status);
        $this->assertTrue($supervisor->isSupervisor());
    }

    public function test_rejection_requires_reason_and_odometer_cannot_regress(): void
    {
        [$fuelType, $vehicle] = $this->fuelContext(9000);
        $user = $this->user('user');
        $supervisor = $this->user('supervisor');
        $requests = app(FuelRequestService::class);

        $this->expectException(OperationalStateException::class);
        $requests->createDraft($user, [
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'odometer' => 8999,
        ]);

        $request = $requests->createDraft($user, [
            'vehicle_id' => $vehicle->id,
            'fuel_type_id' => $fuelType->id,
            'odometer' => 9001,
        ]);
        $requests->submit($user, $request->id);

        try {
            $requests->reject($supervisor, $request->id, '   ');
            $this->fail('A rejeição sem motivo deveria falhar.');
        } catch (OperationalStateException) {
            $this->assertSame('submitted', $request->fresh()->status);
        }
    }

    public function test_request_numbers_are_sequential(): void
    {
        [$fuelType, $vehicle] = $this->fuelContext(1000);
        $user = $this->user('user');
        $requests = app(FuelRequestService::class);

        $first = $requests->createDraft($user, ['vehicle_id' => $vehicle->id, 'fuel_type_id' => $fuelType->id, 'odometer' => 1001]);
        $second = $requests->createDraft($user, ['vehicle_id' => $vehicle->id, 'fuel_type_id' => $fuelType->id, 'odometer' => 1002]);

        $this->assertSame(1, $first->request_number);
        $this->assertSame(2, $second->request_number);
    }

    private function fuelContext(int $odometer): array
    {
        $fuelType = FuelType::create(['name' => 'Gasolina', 'abbreviation' => 'GAS', 'active' => true]);
        $vehicle = Vehicle::create([
            'plate' => 'ABC1D23',
            'model' => 'Veículo de teste',
            'fuel_type_id' => $fuelType->id,
            'current_odometer' => $odometer,
            'status' => 'active',
        ]);

        return [$fuelType, $vehicle];
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => "Usuário $role",
            'username' => "{$role}_".uniqid(),
            'password' => 'SenhaLocal!12345',
            'auth_source' => 'local',
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
