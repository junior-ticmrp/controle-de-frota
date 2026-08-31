<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationalPlaceholderController extends Controller
{
    public function show(Request $request, string $module): View
    {
        $modules = [
            'requisicoes' => [
                'title' => 'Requisições de combustível',
                'description' => 'Fluxo de rascunho, envio, aprovação, rejeição, cancelamento e cumprimento.',
            ],
            'abastecimentos' => [
                'title' => 'Abastecimentos',
                'description' => 'Registro atômico de abastecimento vinculado a requisição aprovada.',
            ],
            'manutencoes' => [
                'title' => 'Manutenções',
                'description' => 'Histórico de serviços, custos e alertas de prazo ou hodômetro.',
            ],
            'relatorios' => [
                'title' => 'Relatórios',
                'description' => 'Consultas filtráveis e impressão em A4 ou cupom térmico de 80 mm.',
            ],
            'cadastros' => [
                'title' => 'Cadastros da frota',
                'description' => 'Pessoas, veículos, combustíveis e preços de referência.',
            ],
        ];

        abort_unless(array_key_exists($module, $modules), 404);

        return view('operational.placeholder', [
            'module' => $modules[$module],
            'user' => $request->attributes->get('fleet.user'),
            'guard' => $request->attributes->get('fleet.guard'),
        ]);
    }
}
