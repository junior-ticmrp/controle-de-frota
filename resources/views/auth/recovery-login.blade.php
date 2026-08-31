<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperação local — Controle de Combustíveis</title>
    <style>body{font-family:system-ui,sans-serif;background:#f7f4ee;margin:0;color:#3e2c14}.card{max-width:420px;margin:10vh auto;background:#fff;padding:32px;border-radius:12px;box-shadow:0 12px 35px #3e2c1420}label,input,button{display:block;width:100%;box-sizing:border-box}label{font-weight:600;margin-top:16px}input{padding:10px;margin-top:6px;border:1px solid #c0ad8e;border-radius:6px}button{margin-top:24px;padding:11px;background:#825b1a;color:#fff;border:0;border-radius:6px;font-weight:700}.error{color:#a61b1b;margin-top:12px}.muted{color:#746852;font-size:.92rem}a{color:#825b1a}</style>
</head>
<body><main class="card"><h1>Recuperação local</h1><p class="muted">Uso exclusivo para contingência administrativa. Não use senha do Active Directory.</p>
@if ($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
<form method="post" action="{{ route('recovery.login.store') }}">@csrf
<label for="username">Usuário local</label><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" required autofocus>
<label for="password">Senha local</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<label class="muted"><input name="remember" type="checkbox" value="1" style="width:auto;display:inline"> Manter sessão</label>
<button type="submit">Entrar em recuperação</button></form>
<p class="muted"><a href="{{ route('login') }}">Voltar ao acesso corporativo</a></p></main></body></html>
