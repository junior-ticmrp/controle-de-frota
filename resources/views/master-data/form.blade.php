@extends('layouts.app', ['title' => ($record ? 'Editar ' : 'Novo ').$config['singular'].' · Frota Câmara'])
@section('content')
<p class="page-kicker">Cadastro operacional</p><h1>{{ $record ? 'Editar' : 'Novo(a)' }} {{ $config['singular'] }}</h1>
<section class="surface"><form method="post" action="{{ $record ? route('master-data.update',['resource'=>$resource,'record'=>$record->id]) : route('master-data.store',['resource'=>$resource]) }}" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px">@csrf @if($record) @method('put') @endif
@foreach($config['fields'] as $field=>$definition)
@php
    $label = $definition[0];
    $type = $definition[1];
    $options = $definition[2] ?? null;
    $value = $field === 'maintenance_mode' ? old($field, $record?->status === 'maintenance') : old($field, $record?->{$field});
    if ($field === 'effective_at' && $record?->effective_at) $value = $record->effective_at->format('Y-m-d\TH:i');
@endphp
<label style="display:grid;gap:7px;{{ $field==='full_name'||$field==='model' ? 'grid-column:1/-1' : '' }}"><span style="font-weight:700;color:#17354f">{{ $label }}</span>
@if($type==='checkbox')<input type="hidden" name="{{ $field }}" value="0"><span style="display:flex;align-items:center;gap:9px;padding:11px;border:1px solid #cbd4cd;border-radius:7px"><input type="checkbox" name="{{ $field }}" value="1" @checked((bool)$value)><span>Marcar veículo como em manutenção. Enquanto marcado, ele não poderá receber novas requisições.</span></span>
@elseif($type==='select')<select name="{{ $field }}" style="padding:11px;border:1px solid #cbd4cd;border-radius:7px">@foreach(is_callable($options)?$options():$options as $key=>$text)<option value="{{ $key }}" @selected((string)$value===(string)$key)>{{ $text }}</option>@endforeach</select>
@else<input type="{{ $type }}" name="{{ $field }}" value="{{ $value }}" @if($type==='number') step="{{ in_array($field,['valor_bruto','desconto']) ? '0.001' : '1' }}" @endif style="padding:11px;border:1px solid #cbd4cd;border-radius:7px">@endif
@error($field)<small style="color:#b42318">{{ $message }}</small>@enderror</label>
@endforeach
<div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap"><button type="submit" style="padding:11px 16px;border:0;border-radius:7px;background:#1f6a60;color:#fff;font-weight:750">Salvar cadastro</button>@if($resource==='veiculos'&&$record)<a href="{{ route('vehicle-responsibilities.edit',$record) }}" style="padding:11px 16px;border:1px solid #b9cac3;border-radius:7px;color:#17354f;font-weight:700">Responsáveis e setores</a>@endif<a href="{{ route('master-data.list',['resource'=>$resource]) }}" style="padding:11px 16px;color:#17354f">Cancelar</a></div></form>
@if($resource==='veiculos'&&$record&&$record->status==='maintenance')<p class="notice"><strong>Veículo em manutenção.</strong> Ele está indisponível para novas requisições até que a caixa de manutenção seja desmarcada e o cadastro salvo.</p>@endif
@if($resource==='precos')<p class="notice"><strong>Histórico preservado.</strong> O preço de referência é calculado como preço bruto menos desconto. Correções devem ser feitas por uma nova vigência, sem reescrever períodos já registrados.</p>@endif
</section>
@endsection
