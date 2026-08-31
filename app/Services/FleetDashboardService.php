<?php

namespace App\Services;

use App\Models\Fueling;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class FleetDashboardService
{
    public function summary(CarbonInterface $referenceDate): array
    {
        $periodStart = $referenceDate->copy()->startOfMonth();
        $periodEnd = $referenceDate->copy()->endOfMonth();

        $fuelingTotals = Fueling::query()
            ->whereBetween('fueling_at', [$periodStart, $periodEnd])
            ->where('legacy_date_unreliable', false)
            ->selectRaw('COALESCE(SUM(liters), 0) as liters, COALESCE(SUM(total_amount), 0) as amount')
            ->first();

        $maintenanceDue = MaintenanceRecord::query()
            ->join('vehicles', 'vehicles.id', '=', 'maintenance_records.vehicle_id')
            ->where(function ($query) use ($referenceDate) {
                $query->whereNotNull('maintenance_records.next_maintenance_at')
                    ->where('maintenance_records.next_maintenance_at', '<=', $referenceDate)
                    ->orWhere(function ($nested) {
                        $nested->whereNotNull('maintenance_records.next_maintenance_odometer')
                            ->whereColumn('maintenance_records.next_maintenance_odometer', '<=', 'vehicles.current_odometer');
                    });
            })
            ->distinct('maintenance_records.id')
            ->count('maintenance_records.id');

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'fueling_liters' => (string) $fuelingTotals->liters,
            'fueling_amount' => (string) $fuelingTotals->amount,
            'active_vehicles' => Vehicle::query()->where('status', 'active')->count(),
            'maintenance_vehicles' => Vehicle::query()->where('status', 'maintenance')->count(),
            'inactive_vehicles' => Vehicle::query()->where('status', 'inactive')->count(),
            'maintenance_due' => $maintenanceDue,
        ];
    }

    public function costEvolution(CarbonInterface $start, CarbonInterface $end): array
    {
        return Fueling::query()
            ->whereBetween('fueling_at', [$start, $end])
            ->where('legacy_date_unreliable', false)
            ->selectRaw('DATE(fueling_at) as date, SUM(total_amount) as amount')
            ->groupBy(DB::raw('DATE(fueling_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => ['date' => $row->date, 'amount' => (string) $row->amount])
            ->all();
    }
}
