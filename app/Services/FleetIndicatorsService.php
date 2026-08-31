<?php
namespace App\Services;

use App\Models\Fueling;
use App\Models\MaintenanceRecord;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FleetIndicatorsService
{
    public function period(string $preset, ?string $month): array
    {
        $now = now();
        if ($preset === 'semester') return ['preset' => 'semester', 'month' => null, 'start' => $now->copy()->startOfMonth()->subMonths(5), 'end' => $now->copy()->endOfMonth(), 'label' => 'Último semestre'];
        if ($preset === 'year') return ['preset' => 'year', 'month' => null, 'start' => $now->copy()->startOfMonth()->subMonths(11), 'end' => $now->copy()->endOfMonth(), 'label' => 'Último ano'];
        $base = $month && preg_match('/^\d{4}-\d{2}$/', $month) ? Carbon::createFromFormat('Y-m', $month) : $now;
        return ['preset' => 'month', 'month' => $base->format('Y-m'), 'start' => $base->copy()->startOfMonth(), 'end' => $base->copy()->endOfMonth(), 'label' => $base->locale('pt_BR')->translatedFormat('F de Y')];
    }

    public function summary(Carbon $start, Carbon $end): array
    {
        $fuelings = Fueling::query()->where('legacy_date_unreliable', false)->whereBetween('fueling_at', [$start, $end]);
        $dashboard = app(FleetDashboardService::class)->summary($end);
        return [
            'fueling_liters' => (float) ((clone $fuelings)->sum('liters') ?? 0),
            'fueling_amount' => (float) ((clone $fuelings)->sum('total_amount') ?? 0),
            'active_vehicles' => (int) ($dashboard['active_vehicles'] ?? Vehicle::query()->where('status', 'active')->count()),
            'maintenance_due' => (int) ($dashboard['maintenance_due'] ?? 0),
            'maintenance_vehicles' => (int) ($dashboard['maintenance_vehicles'] ?? Vehicle::query()->where('status', 'maintenance')->count()),
            'inactive_vehicles' => (int) ($dashboard['inactive_vehicles'] ?? Vehicle::query()->where('status', 'inactive')->count()),
        ];
    }

    public function evolution(Carbon $start, Carbon $end): array
    {
        return Fueling::query()->where('legacy_date_unreliable', false)->whereBetween('fueling_at', [$start, $end])
            ->selectRaw('DATE(fueling_at) as date, SUM(total_amount) as amount')
            ->groupByRaw('DATE(fueling_at)')->orderBy('date')->get()
            ->map(fn ($row) => ['date' => $row->date, 'amount' => (float) $row->amount])->all();
    }

    public function byResponsible(Carbon $start, Carbon $end): array
    {
        return DB::table('fuelings')
            ->join('fuel_requests', 'fuel_requests.id', '=', 'fuelings.request_id')
            ->leftJoin('people', 'people.id', '=', 'fuel_requests.requester_person_id')
            ->where('fuelings.legacy_date_unreliable', false)
            ->whereBetween('fuelings.fueling_at', [$start, $end])
            ->selectRaw("COALESCE(people.full_name, fuel_requests.responsible_sector, 'Sem responsável registrado') as responsible, SUM(fuelings.liters) as liters, SUM(fuelings.total_amount) as amount")
            ->groupBy('people.full_name', 'fuel_requests.responsible_sector')
            ->orderByDesc('amount')->get()
            ->map(fn ($row) => ['responsible' => $row->responsible, 'liters' => (float) $row->liters, 'amount' => (float) $row->amount])->all();
    }
}
