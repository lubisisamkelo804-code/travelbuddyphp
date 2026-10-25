@extends('layouts.auth')
@section('title', 'Forgot password')
@section('auth-content')
<h1 class="h-display h2">Forgot your password?</h1>
<p class="sub">No worries. Enter your email and we'll send you a link to reset it.</p>
@if(session('success'))<div class="errors" style="background:var(--sage,#e7f5ec);color:#1e6b40">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" class="stack-form">
    @csrf
    <div class="field"><label>Email address</label>
        <input class="input" type="email" name="email" value="{{ old('email') }}" placeholder="you@wander.co" required autofocus></div>
    <button class="btn btn-primary btn-block" type="submit"><i class="fa-solid fa-paper-plane"></i> Email password reset link</button>
</form>
<p class="auth-swap"><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left"></i> Back to log in</a></p>
@endsection
