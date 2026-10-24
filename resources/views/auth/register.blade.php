@extends('layouts.auth')
@section('title', 'Create account')
@section('auth-content')
<h1 class="h-display h2">Begin your story</h1>
<p class="sub">Join a community of explorers sharing the world's best-kept secrets.</p>
@if($errors->any())<div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('register') }}" class="stack-form">
    @csrf
    <div class="grid-2">
        <div class="field"><label>Full name</label><input class="input" type="text" name="name" value="{{ old('name') }}" required></div>
        <div class="field"><label>Username</label><input class="input" type="text" name="username" value="{{ old('username') }}" required></div>
    </div>
    <div class="field"><label>Email</label><input class="input" type="email" name="email" value="{{ old('email') }}" required></div>
    <div class="grid-2">
        <div class="field"><label>Password</label><input class="input" type="password" name="password" required></div>
        <div class="field"><label>Confirm</label><input class="input" type="password" name="password_confirmation" required></div>
    </div>
    <div class="field"><label>I'm joining as</label>
        <div class="role-cards">
            @foreach(['visitor' => ['fa-compass', 'Adventure Seeker', 'Discover, book & review places'],
                      'destination_owner' => ['fa-map-location-dot', 'Destination Owner', 'List & grow my own destinations'],
                      'event_creator' => ['fa-calendar-days', 'Event Creator', 'Host experiences & manage guests']] as $value => [$icon, $label, $desc])
            <label class="role-card">
                <input type="radio" name="role" value="{{ $value }}" {{ old('role', 'visitor') === $value ? 'checked' : '' }}>
                <span><b><i class="fa-solid {{ $icon }}"></i> {{ $label }}</b><span>{{ $desc }}</span></span>
            </label>
            @endforeach
        </div>
    </div>
    <button class="btn btn-accent btn-block" type="submit"><i class="fa-solid fa-feather-pointed"></i> Create account</button>
</form>
<div class="auth-divider">OR</div>
<a class="google-btn" href="{{ route('google') }}"><i class="fa-brands fa-google"></i> Sign up with Google</a>
<p class="auth-swap">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
@endsection