<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Authorize access</title></head>
<body><main><h1>Authorize {{ $client->name }}</h1>
<p>Signed in as {{ $user->email }}. This application requests access to your account.</p>
<ul>@foreach ($scopes as $scope)<li>{{ $scope->description }}</li>@endforeach</ul>
<form method="POST" action="{{ route('passport.authorizations.approve') }}">
    @csrf
    <input type="hidden" name="auth_token" value="{{ $authToken }}">
    <button type="submit">Allow access</button>
</form>
<form method="POST" action="{{ route('passport.authorizations.deny') }}">
    @csrf @method('DELETE')
    <input type="hidden" name="auth_token" value="{{ $authToken }}">
    <button type="submit">Deny access</button>
</form>
<form method="POST" action="{{ route('session.logout') }}">@csrf<button type="submit">Sign out</button></form>
</main></body></html>
