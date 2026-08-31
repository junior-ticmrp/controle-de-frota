<?php

namespace App\Services;

use App\Models\MaintenanceRecord;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function record(User $actor, array $attributes): MaintenanceRecord
    {
        if (! $actor->is_active || ! $actor->isSupervisor()) {
            throw new OperationalPermissionException('Registro de manutenção permitido somente a supervisor operacional ativo.');
        }

        return DB::transaction(function () use ($actor, $attributes) {
            $vehicle = Vehicle::query()->lockForUpdate()->findOrFail($attributes['vehicle_id']);
            $odometer = (int) $attributes['odometer'];
            $cost = (float) $attributes['cost'];

            if ($odometer < (int) $vehicle->current_odometer) {
                throw new OperationalStateException('O hodômetro da manutenção não pode ser inferior ao hodômetro atual do veículo.');
            }

            if ($cost < 0) {
                throw new OperationalStateException('O custo da manutenção não pode ser negativo.');
            }

            $record = MaintenanceRecord::create([
                'vehicle_id' => $vehicle->id,
                'service_type' => trim($attributes['service_type']),
                'performed_at' => $attributes['performed_at'] ?? now(),
                'odometer' => $odometer,
                'cost' => $attributes['cost'],
                'next_maintenance_at' => $attributes['next_maintenance_at'] ?? null,
                'next_maintenance_odometer' => $attributes['next_maintenance_odometer'] ?? null,
                'provider' => $attributes['provider'] ?? null,
                'notes' => $attributes['notes'] ?? null,
                'created_by_user_id' => $actor->id,
            ]);

            if ($odometer > (int) $vehicle->current_odometer) {
                $vehicle->update(['current_odometer' => $odometer]);
            }

            return $record->fresh();
        });
    }
}
