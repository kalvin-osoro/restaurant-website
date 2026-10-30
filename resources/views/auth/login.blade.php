<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in</title></head>
<body><main><h1>Sign in to {{ config('app.name') }}</h1>
<form method="POST" action="{{ route('login.store') }}">
    @csrf
    <p><label>Email <input type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></label></p>
    <p><label>Password <input type="password" name="password" required autocomplete="current-password"></label></p>
    @error('email')<p role="alert">{{ $message }}</p>@enderror
    @error('password')<p role="alert">{{ $message }}</p>@enderror
    <button type="submit">Sign in</button>
</form></main></body></html>
