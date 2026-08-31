@extends('layouts.app',['title'=>'Relatórios · Frota Câmara'])
@section('content')
<p class="page-kicker">Relatório de abastecimentos</p><h1>Consultas e impressão</h1><p class="lede">Filtre por veículo, motorista, setor ou gabinete responsável e período. Para registros históricos, o motorista da requisição também é considerado quando o abastecimento não o informa diretamente.</p>
<section class="surface">
<style>
.report-filter-shell{width:100%;}
.report-filter-row{display:grid;grid-template-columns:minmax(130px,1.05fr) minmax(140px,1.1fr) minmax(120px,1fr) minmax(150px,1.25fr) minmax(120px,.95fr) minmax(120px,.95fr) auto;align-items:end;gap:8px;width:100%;}
.report-filter-row>label,.report-filter-field{display:grid;min-width:0!important;gap:4px;}
.report-filter-row>label>span,.report-filter-label{overflow:hidden;color:#17354f;font-size:12px!important;font-weight:700;line-height:1.2;white-space:nowrap;text-overflow:ellipsis;}
.report-filter-row select,.report-filter-row input{width:100%;min-width:0!important;height:38px!important;padding:6px 8px!important;border:1px solid #d7ddd7;border-radius:7px;background:#fff;color:#17354f;font-size:13px!important;line-height:1.2;}
.report-filter-row .report-filter-actions{display:flex;align-items:stretch;gap:6px;white-space:nowrap;}
.report-filter-row .report-filter-actions button,.report-filter-row .report-filter-actions a{display:inline-flex;align-items:center;justify-content:center;height:38px;padding:7px 9px;border:0;border-radius:7px;font-size:13px!important;font-weight:700;line-height:1.2;}
.report-filter-row .report-filter-actions button{color:#fff;background:#1f6a60;cursor:pointer;}
.report-filter-row .report-filter-actions a{color:#fff;background:#17354f;}
.report-plate-preview{margin-top:12px;}
@media(max-width:1100px){.report-filter-row{grid-template-columns:repeat(4,minmax(0,1fr));}.report-filter-row .report-filter-actions{grid-column:1/-1;justify-content:flex-start;}}
@media(max-width:650px){.report-filter-row{grid-template-columns:repeat(2,minmax(0,1fr));}.report-filter-row .report-filter-actions{grid-column:1/-1;flex-wrap:wrap;}.report-filter-row .report-filter-actions button,.report-filter-row .report-filter-actions a{flex:1 1 170px;}}
</style>
<div class="report-filter-shell">
<form method="get" class="report-filter-row" aria-label="Filtros do relatório">
    @include('components.vehicle-status-filter',['selected'=>$filters['vehicle_status']??'active'])
    <label><span>Veículo</span><select name="vehicle_id"><option value="">Todos</option>@foreach($vehicles as $v)<option value="{{ $v->id }}" data-plate="{{ $v->plate }}" @selected(($filters['vehicle_id']??null)==$v->id)>{{ $v->plate }} · {{ $v->model }}</option>@endforeach</select></label>
    <label><span>Motorista</span><select name="driver_person_id"><option value="">Todos</option>@foreach($drivers as $p)<option value="{{ $p->id }}" @selected(($filters['driver_person_id']??null)==$p->id)>{{ $p->full_name }}</option>@endforeach</select></label>
    <label><span>Setor/Gabinete Responsável</span><select name="responsible"><option value="">Todos</option>@foreach($responsibleOptions as $responsibleOption)<option value="{{ $responsibleOption }}" @selected(($filters['responsible']??'')===$responsibleOption)>{{ $responsibleOption }}</option>@endforeach</select></label>
    <label><span>Data inicial</span><input type="date" name="start_date" value="{{ $filters['start_date']??'' }}"></label>
    <label><span>Data final</span><input type="date" name="end_date" value="{{ $filters['end_date']??'' }}"></label>
    <div class="report-filter-actions"><button type="submit">Aplicar filtros</button><a href="{{ route('reports.pdf',request()->query()) }}">Baixar PDF A4</a></div>
</form>
<div id="report-plate-preview" class="vehicle-plate-preview report-plate-preview" aria-live="polite" hidden><span class="vehicle-plate" aria-label="Placa do veículo" style="position:relative;display:inline-flex;flex-direction:column;align-items:center;justify-content:flex-end;width:270px;min-width:270px;min-height:120px;padding:42px 15px 13px;border:8px solid #050505;border-radius:14px;background:#ededed;box-shadow:inset 0 0 0 3px #c6c6c6,0 3px 7px rgba(0,0,0,.18);color:#050505;font-family:Arial Black,Arial,Helvetica,sans-serif;font-size:44px;font-weight:900;letter-spacing:3px;line-height:1;text-align:center;white-space:nowrap;overflow:hidden;box-sizing:border-box"><span id="report-plate-title" aria-hidden="true" style="position:absolute;top:7px;left:0;right:0;color:#171db3;font-family:Arial,Helvetica,sans-serif;font-size:23px;font-weight:900;letter-spacing:2px;line-height:1;text-align:center">CMRP</span><span id="report-plate-value" style="display:block;line-height:1"></span></span><small id="report-plate-description">Placa do veículo selecionado</small></div>
</div>
<div class="grid" style="margin-top:24px"><article class="card"><h2>{{ number_format((float)($totals->liters ?? 0),3,',','.') }} L</h2><p>Volume no resultado filtrado</p></article><article class="card"><h2>R$ {{ number_format((float)($totals->amount ?? 0),2,',','.') }}</h2><p>Custo no resultado filtrado</p></article></div>
<div style="overflow:auto;margin-top:22px"><table style="width:100%;border-collapse:collapse"><thead><tr><th>Data</th><th>Req.</th><th>Veículo</th><th>Motorista</th><th>Litros</th><th>Total</th><th></th></tr></thead><tbody>@forelse($fuelings as $f)<tr><td>{{ $f->fueling_at->format('d/m/Y H:i') }}</td><td>#{{ $f->fuelRequest?->request_number }}</td><td><span class="vehicle-plate" aria-label="Placa do veículo">{{ $f->vehicle?->plate }}</span></td><td>{{ $f->driver?->full_name ?? $f->fuelRequest?->driver?->full_name ?? '—' }}</td><td>{{ number_format($f->liters,3,',','.') }}</td><td>R$ {{ number_format($f->total_amount,2,',','.') }}</td><td><a style="color:#1f6a60;font-weight:700" href="{{ route('fuelings.receipt',$f) }}">Comprovante</a></td></tr>@empty<tr><td colspan="7" style="padding:28px;text-align:center">Nenhum abastecimento corresponde aos filtros.</td></tr>@endforelse</tbody></table></div>
@include('components.compact-pagination',['paginator'=>$fuelings])</section>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const select=document.querySelector('select[name="vehicle_id"]'),preview=document.getElementById('report-plate-preview'),value=document.getElementById('report-plate-value'),description=document.getElementById('report-plate-description');
 function syncReportPlate(){const option=select?.options?.[select.selectedIndex],plate=option?.dataset?.plate ?? '',label=option?.textContent?.trim() ?? '';preview.hidden=!plate;value.textContent=plate;description.textContent=plate ? 'Veículo selecionado: '+label : 'Placa do veículo selecionado';}
 if(select){select.addEventListener('change',syncReportPlate);syncReportPlate();}
});
</script>
@endsection
