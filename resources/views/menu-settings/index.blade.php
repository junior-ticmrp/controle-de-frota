@extends('layouts.app',['title'=>'Configuração do menu · Frota Câmara'])
@section('content')
<p class="page-kicker">Administração técnica</p>
<h1>Configuração do menu</h1>
<p class="lede">Habilite ou desabilite itens da navegação por perfil. A configuração controla a visibilidade do menu; as permissões das rotas continuam protegidas pelo papel de acesso.</p>
<section class="surface">
  <div class="notice" role="note"><strong>Como funciona:</strong> desmarcar um item remove o atalho do menu para o perfil escolhido, mas não altera dados, rotas ou permissões. O item Configuração do menu permanece disponível para a Administração técnica.</div>
  <form method="post" action="{{ route('menu-settings.update') }}" style="margin-top:22px">
    @csrf
    @method('PUT')
    <div style="overflow:auto">
      <table style="width:100%;border-collapse:collapse;min-width:680px">
        <thead><tr><th style="text-align:left;padding:10px;border-bottom:1px solid #d7ddd7">Item do menu</th>@foreach($roles as $role=>$label)<th style="padding:10px;border-bottom:1px solid #d7ddd7;text-align:center">{{ $label }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach($items as $item)
          <tr>
            <td style="padding:12px 10px;border-bottom:1px solid #e2e6e1"><strong>{{ $item['symbol'] }} · {{ $item['label'] }}</strong><br><small>{{ $item['section'] }}</small></td>
            @foreach($roles as $role=>$label)
              <td style="padding:12px 10px;border-bottom:1px solid #e2e6e1;text-align:center">
                @if(in_array($role,$item['roles'],true))
                  @php($setting=$stored->get($role.'|'.$item['key']))
                  <label style="display:inline-flex;align-items:center;gap:7px;cursor:pointer"><input type="checkbox" name="settings[{{ $role }}][{{ $item['key'] }}]" value="1" @checked($setting?->enabled ?? $item['default'])><span class="sr-only">Mostrar {{ $item['label'] }} para {{ $label }}</span><span aria-hidden="true">{{ ($setting?->enabled ?? $item['default']) ? 'Ativo' : 'Oculto' }}</span></label>
                @else
                  <span style="color:#68736c">Não aplicável</span>
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-top:20px"><button type="submit" style="padding:10px 16px;border:0;border-radius:7px;color:#fff;background:#1f6a60;font:inherit;font-weight:700;cursor:pointer">Salvar configuração</button></div>
  </form>
</section>
@endsection
