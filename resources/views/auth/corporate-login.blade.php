<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acesso corporativo — Controle de Combustíveis</title>
    <style>body{font-family:system-ui,sans-serif;background:#f4f7f5;margin:0;color:#163126}.card{max-width:420px;margin:10vh auto;background:#fff;padding:32px;border-radius:12px;box-shadow:0 12px 35px #16312620}label,input,button{display:block;width:100%;box-sizing:border-box}label{font-weight:600;margin-top:16px}input{padding:10px;margin-top:6px;border:1px solid #a8b7af;border-radius:6px}button{margin-top:24px;padding:11px;background:#176b42;color:white;border:0;border-radius:6px;font-weight:700}.error{color:#a61b1b;margin-top:12px}.muted{color:#60746a;font-size:.92rem}a{color:#176b42}</style>
</head>
<body><main class="card"><h1>Controle de Combustíveis</h1><p class="muted">Acesso corporativo via Active Directory.</p>
@if ($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
<form method="post" action="{{ route('login.store') }}">@csrf
<label for="username">Usuário de rede</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
<label for="password">Senha corporativa</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<label class="muted"><input name="remember" type="checkbox" value="1" style="width:auto;display:inline"> Manter sessão</label>
<button type="submit">Entrar</button></form>
<p class="muted"><a href="{{ route('recovery.login') }}">Acesso local de recuperação</a></p></main></body></html>
