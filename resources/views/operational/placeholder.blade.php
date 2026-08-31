@extends('layouts.app', ['title' => $module['title'].' · Frota Câmara'])

@section('content')
    <p class="page-kicker">Módulo operacional</p>
    <h1>{{ $module['title'] }}</h1>
    <p class="lede">{{ $module['description'] }}</p>
    <section class="surface">
        <div class="notice">
            <strong>Base de navegação concluída.</strong> A tela específica deste módulo será ativada na próxima entrega, mantendo esta rota protegida pela sessão corporativa ou de recuperação local e pelo papel apropriado.
        </div>
        <p style="margin:20px 0 0"><a href="{{ route('dashboard') }}" style="color:#1f6a60;font-weight:750">Voltar ao painel</a></p>
    </section>
@endsection
