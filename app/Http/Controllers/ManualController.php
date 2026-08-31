<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\View\View;
class ManualController extends Controller
{
 public function __invoke(Request $request): View
 {
   $user = $request->attributes->get('fleet.user');
   $manuals = [
       'user' => ['key' => 'usuario', 'title' => 'Manual do Usuário', 'intro' => 'Orientações para criar, acompanhar e consultar requisições.'],
       'supervisor' => ['key' => 'operador', 'title' => 'Manual do Operador', 'intro' => 'Orientações para análise, aprovação, abastecimento e operação da frota.'],
       'admin' => ['key' => 'tecnico', 'title' => 'Manual Técnico', 'intro' => 'Orientações para Administração técnica, segurança, menu, infraestrutura e suporte.'],
   ];
   $manual = $manuals[$user?->role] ?? $manuals['user'];
   $data = [
       'user' => $user,
       'guard' => $request->attributes->get('fleet.guard'),
       'manual' => $manual,
       'manualRole' => $user?->role ?? 'user',
   ];
   return view($request->boolean('embed') ? 'manual.embed' : 'manual.index', $data);
 }
}
