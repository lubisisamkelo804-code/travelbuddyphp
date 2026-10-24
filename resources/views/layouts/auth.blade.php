<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>TravelBuddy — @yield('title', 'Welcome')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Manrope:wght@400..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/tb.css') }}">
<link rel="stylesheet" href="{{ asset('css/tb-v2.css') }}">
</head>
<body>
<div class="auth-split">
    <aside class="auth-left">
        <a href="{{ route('home') }}" class="brand">
            @include('partials.logo', ['class' => 'logo-lg', 'id' => 'al'])
            <span class="brand-name">TravelBuddy</span>
        </a>
        <div>
            <h1 class="auth-quote">Every journey begins with a <em>single pin.</em></h1>
            <p>Discover hand-loved destinations, join unforgettable group experiences, and travel further — together.</p>
            <div class="auth-stats">
                <div><b>{{ \App\Models\Destination::count() }}+</b><span>Destinations</span></div>
                <div><b>{{ \App\Models\Event::count() }}+</b><span>Experiences</span></div>
                <div><b>{{ \App\Models\User::count() }}+</b><span>Travelers</span></div>
            </div>
        </div>
        <div class="auth-foot">© {{ date('Y') }} TravelBuddy · Crafted for explorers</div>
    </aside>
    <main class="auth-right">
        <div class="auth-card">
            <div class="mobile-auth-head">
                @include('partials.logo', ['class' => 'logo-md', 'id' => 'am'])
                <span class="brand-name" style="color:var(--espresso)">TravelBuddy</span>
            </div>
            @yield('auth-content')
        </div>
    </main>
</div>
<div class="tb-toast" id="toast"></div>
<script src="{{ asset('js/tb.js') }}"></script>
<script>
@if(session('success')) showToast(@json(session('success')), 'fa-circle-check'); @endif
@if(session('error')) showToast(@json(session('error')), 'fa-circle-exclamation'); @endif
</script>
</body>
</html>