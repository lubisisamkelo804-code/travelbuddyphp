@extends('layouts.auth')
@section('title', 'Reset password')
@section('auth-content')
<h1 class="h-display h2">Reset your password</h1>
<p class="sub">Choose a new password for your account.</p>
@if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.update') }}" class="stack-form">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="field"><label>Email address</label>
        <input class="input" type="email" name="email" value="{{ old('email', $email) }}" placeholder="you@wander.co" required autofocus></div>
    <div class="field"><label>New password</label>
        <input class="input" type="password" name="password" placeholder="••••••••" required autocomplete="new-password"></div>
    <div class="field"><label>Confirm new password</label>
        <input class="input" type="password" name="password_confirmation" placeholder="••••••••" required autocomplete="new-password"></div>
    <button class="btn btn-primary btn-block" type="submit"><i class="fa-solid fa-key"></i> Reset password</button>
</form>
<p class="auth-swap"><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to log in</a></p>
@endsection
