@extends('layouts.auth')
@section('title', 'Log in')
@section('auth-content')
<h1 class="h-display h2">Welcome back</h1>
<p class="sub">Log in to continue your journey.</p>
@if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('login') }}" class="stack-form">
    @csrf
    <div class="field"><label>Email or username</label>
        <input class="input" type="text" name="email" value="{{ old('email') }}" placeholder="you@wander.co" required autofocus></div>
    <div class="field"><label>Password</label>
        <input class="input" type="password" name="password" placeholder="••••••••" required></div>
    <label class="check"><input type="checkbox" name="remember"> Remember me</label>
    <button class="btn btn-primary btn-block" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Log in</button>
</form>
<div class="auth-divider">OR</div>
<a class="google-btn" href="{{ route('google') }}"><i class="fa-brands fa-google"></i> Continue with Google</a>
<p class="auth-swap">New to TravelBuddy? <a href="{{ route('register') }}">Create an account</a></p>
@endsection