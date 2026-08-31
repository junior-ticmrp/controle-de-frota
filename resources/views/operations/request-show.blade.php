@extends('layouts.app',['title'=>'Requisição #'.$fuelRequest->request_number.' · Frota Câmara'])

@section('content')
<p class="page-kicker">Requisição #{{ $fuelRequest->request_number }}</p>
<h1>{{ $statuses[$fuelRequest->status] ?? 'Situação não informada' }}</h1>
<p class="lede"><span class="vehicle-plate" aria-label="Placa do veículo">{{ $fuelRequest->vehicle?->plate ?? '—' }}</span> · {{ $fuelRequest->vehicle?->model ?? 'Veículo não informado' }} · {{ $fuelRequest->fuelType?->name ?? 'Combustível não informado' }}</p>

<section class="surface">
  <div class="grid">
    <article class="card">
      <h2>Solicitação</h2>
      <p>
        {{ $fuelRequest->requested_at?->format('d/m/Y H:i') ?? '—' }}<br>
        Hodômetro: {{ number_format((int) $fuelRequest->odometer,0,',','.') }} km<br>
        Litros estimados: {{ $fuelRequest->requested_liters ?? '—' }}<br>
        Valor estimado: {{ $fuelRequest->estimated_amount !== null ? 'R$ '.number_format((float) $fuelRequest->estimated_amount,2,',','.') : '—' }}
      </p>
    </article>
    <article class="card">
      <h2>Responsáveis</h2>
      <p>
        Solicitante: {{ $fuelRequest->requester?->full_name ?? '—' }}<br>
        Motorista: {{ $fuelRequest->driver?->full_name ?? '—' }}<br>
        Criador: {{ $fuelRequest->creator?->name ?? '—' }}
      </p>
    </article>
    <article class="card">
      <h2>Operação</h2>
      <p>
        Aprovador: {{ $fuelRequest->approver?->name ?? '—' }}<br>
        {{ $fuelRequest->rejection_reason ? 'Motivo: '.$fuelRequest->rejection_reason : 'Sem decisão de operação registrada.' }}
      </p>
    </article>
    @if($fuelRequest->requiresAuthorization())
      <article class="card">
        <h2>Autorização</h2>
        <p>
          Autorizada em: {{ $fuelRequest->authorization_at?->format('d/m/Y H:i') ?? '—' }}<br>
          Autorização válida até: {{ $fuelRequest->authorization_expires_at?->format('d/m/Y H:i') ?? 'Aguardando aprovação' }}<br>
          {{ $fuelRequest->status === 'approved' && ! $fuelRequest->hasActiveAuthorization() ? 'Autorização expirada.' : '' }}
        </p>
      </article>
    @endif
  </div>

  @if($errors->any())
    <div class="flash">{{ $errors->first() }}</div>
  @endif

  <div style="display:flex;gap:9px;flex-wrap:wrap;margin-top:20px">
    @if($fuelRequest->requiresAuthorization())
      <a href="{{ route('fuel-requests.authorization',$fuelRequest) }}" target="_blank" rel="noopener" style="padding:8px 11px;border:1px solid #b9cac3;color:#17354f;border-radius:7px;font-weight:700">Autorização - 3 vias A4</a>
      <a href="{{ route('fuel-requests.authorization-cupom',$fuelRequest) }}" target="_blank" rel="noopener" style="padding:8px 11px;border:1px solid #b9cac3;color:#17354f;border-radius:7px;font-weight:700">Autorização - 3 vias Cupom</a>
    @endif

    @if($fuelRequest->status === 'draft' && ($user->role === 'supervisor' || $fuelRequest->created_by_user_id === $user->id))
      <form method="post" action="{{ route('fuel-requests.submit',$fuelRequest) }}">@csrf<button>Enviar</button></form>
    @endif

    @if($user->role === 'supervisor' && $fuelRequest->status === 'submitted')
      <form method="post" action="{{ route('fuel-requests.approve',$fuelRequest) }}">@csrf<button>Aprovar</button></form>
      <form method="post" action="{{ route('fuel-requests.reject',$fuelRequest) }}">@csrf<input name="rejection_reason" placeholder="Motivo obrigatório"><button>Rejeitar</button></form>
    @endif

    @if(in_array($fuelRequest->status,['draft','submitted'],true))
      <form method="post" action="{{ route('fuel-requests.cancel',$fuelRequest) }}">@csrf<button>Cancelar</button></form>
    @endif

    @if($user->role === 'supervisor' && $fuelRequest->status === 'approved')
      <a href="{{ route('fuelings.create',$fuelRequest) }}" style="padding:8px 11px;background:#1f6a60;color:#fff;border-radius:7px">Registrar abastecimento</a>
    @endif
  </div>
</section>
@endsection
