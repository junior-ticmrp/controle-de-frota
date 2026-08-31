<?php
namespace App\Http\Controllers;
use App\Services\FleetDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
class DashboardController extends Controller {
 public function __invoke(Request $request, FleetDashboardService $fleet): View {
  $reference=now();
  return view('dashboard.home',['user'=>$request->attributes->get('fleet.user'),'guard'=>$request->attributes->get('fleet.guard'),'summary'=>$fleet->summary($reference),'evolution'=>$fleet->costEvolution($reference->copy()->subDays(30),$reference)]);
 }
}
