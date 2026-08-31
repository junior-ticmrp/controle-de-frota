<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FleetIndicatorsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndicatorsController extends Controller
{
    public function __invoke(Request $request, FleetIndicatorsService $indicators): View
    {
        $data = $request->validate(['period' => ['nullable', 'in:month,semester,year'], 'month' => ['nullable', 'regex:/^\d{4}-\d{2}$/']]);
        $period = $indicators->period($data['period'] ?? 'month', $data['month'] ?? null);
        return view('indicators.index', [
            'user' => $request->attributes->get('fleet.user'),
            'guard' => $request->attributes->get('fleet.guard'),
            'period' => $period,
            'summary' => $indicators->summary($period['start'], $period['end']),
            'evolution' => $indicators->evolution($period['start'], $period['end']),
            'responsibles' => $indicators->byResponsible($period['start'], $period['end']),
        ]);
    }
}
