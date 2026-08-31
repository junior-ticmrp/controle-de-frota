<p class="page-kicker">Ajuda e operação</p>
<h1>{{ $manual['title'] }}</h1>
<p class="lede">{{ $manual['intro'] }} O conteúdo desta página é ajustado automaticamente ao seu perfil de acesso.</p>
<div class="manual-profile-badge" role="status">Perfil exibido: <strong>{{ $manualRole === 'admin' ? 'Administração técnica' : ($manualRole === 'supervisor' ? 'Operador' : 'Usuário') }}</strong></div>
<div class="manual-actions"><button type="button" onclick="window.print()">Imprimir esta página</button></div>
@include('manual.partials.'.$manual['key'])
