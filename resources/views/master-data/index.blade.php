@extends('layouts.app', ['title' => ($config['title'] ?? 'Cadastros').' · Frota Câmara'])
@section('content')
<p class="page-kicker">Cadastros operacionais</p>
@if(!$resource)
  <h1>Dados mestres da frota</h1><p class="lede">Cadastre e mantenha os dados que sustentam requisições, abastecimentos e manutenções. Exclusões não são oferecidas para preservar a rastreabilidade.</p>
  <section class="surface"><div class="grid">
  @foreach(['pessoas'=>['Pessoas','Motoristas, vereadores e servidores.'],'combustiveis'=>['Combustíveis','Tipos de combustível ativos.'],'precos'=>['Preços de referência','Histórico de vigências e descontos.'],'veiculos'=>['Veículos','Placa, hodômetro e manutenção.']] as $key=>$item)
    <article class="card"><span class="module-icon">{{ strtoupper(substr($item[0],0,2)) }}</span><h2>{{ $item[0] }}</h2><p>{{ $item[1] }}</p><a href="{{ route('master-data.list',['resource'=>$key]) }}">Gerenciar cadastro</a></article>
  @endforeach
  </div></section>
@else
  <h1>{{ $config['title'] }}</h1><p class="lede">Registro e consulta protegidos por papel operador, com eventos de auditoria para criação e alteração.</p>
  <section class="surface">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:end;flex-wrap:wrap"><form method="get" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap"><input name="q" value="{{ request('q') }}" placeholder="Pesquisar" style="padding:10px;border:1px solid #d7ddd7;border-radius:7px">@if($resource==='veiculos') @include('components.vehicle-status-filter',['selected'=>$vehicleStatus]) @endif<button class="logout" style="width:auto;background:#17354f" type="submit">Pesquisar</button></form><a href="{{ route('master-data.create',['resource'=>$resource]) }}" style="padding:10px 13px;background:#1f6a60;color:#fff;border-radius:7px;font-weight:700">Novo cadastro</a></div>
    <div style="overflow:auto;margin-top:20px"><table style="width:100%;border-collapse:collapse;font-size:14px"><thead><tr>@foreach($config['columns'] as $label)<th style="text-align:left;padding:11px;border-bottom:2px solid #d7ddd7">{{ $label }}</th>@endforeach<th style="padding:11px;border-bottom:2px solid #d7ddd7"></th></tr></thead><tbody>
      @forelse($records as $record)<tr>@foreach($config['columns'] as $field=>$label)<td style="padding:12px 11px;border-bottom:1px solid #e5e8e3">@php($value=data_get($record,$field)) @if($field==='active') {{ $value ? 'Ativo' : 'Inativo' }} @elseif($field==='status') {{ ['active'=>'Ativo','maintenance'=>'Em manutenção','inactive'=>'Inativo'][$value] ?? $value }} @else {{ $value instanceof \Carbon\Carbon ? $value->format('d/m/Y H:i') : $value }} @endif</td>@endforeach<td style="padding:12px 11px"><a href="{{ route('master-data.edit',['resource'=>$resource,'record'=>$record->id]) }}" style="color:#1f6a60;font-weight:700">Editar</a></td></tr>@empty<tr><td colspan="{{ count($config['columns'])+1 }}" style="padding:28px;text-align:center;color:#64716c">Nenhum registro encontrado.</td></tr>@endforelse
    </tbody></table></div>{{ $records->links() }}
  </section>
@endif
@endsection
