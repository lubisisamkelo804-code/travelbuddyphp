<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>TravelBuddy — @yield('title', 'Home')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Manrope:wght@400..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<meta name="reverb-key" content="{{ config('broadcasting.connections.reverb.key') }}">
<meta name="reverb-host" content="{{ config('broadcasting.connections.reverb.host') }}">
<meta name="reverb-port" content="{{ config('broadcasting.connections.reverb.port') }}">
<meta name="reverb-scheme" content="{{ config('broadcasting.connections.reverb.scheme') }}">
<link rel="stylesheet" href="{{ asset('css/tb.css') }}">
<link rel="stylesheet" href="{{ asset('css/tb-v2.css') }}">
@stack('styles')
</head>
<body>
<div class="app-container">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            @include('partials.logo', ['class' => 'logo-md', 'id' => 'sb'])
            <span>TravelBuddy</span>
        </div>
        <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home') ? 'active' : '' }}"><i class="fa-solid fa-house"></i><span>Home Feed</span></a>
        <a href="{{ route('community') }}" class="nav-item {{ request()->routeIs('community') ? 'active' : '' }}"><i class="fa-solid fa-users"></i><span>Community</span></a>
        <a href="{{ route('destinations.index') }}" class="nav-item {{ request()->routeIs('destinations.*') ? 'active' : '' }}"><i class="fa-solid fa-map-location-dot"></i><span>Destinations</span></a>
        <a href="{{ route('events.index') }}" class="nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}"><i class="fa-solid fa-calendar"></i><span>Events</span></a>
        <a href="{{ route('explore') }}" class="nav-item {{ request()->routeIs('explore', 'recommendations') ? 'active' : '' }}"><i class="fa-solid fa-compass"></i><span>Explore</span></a>
        <a href="{{ route('map') }}" class="nav-item {{ request()->routeIs('map') ? 'active' : '' }}"><i class="fa-solid fa-map-location-dot"></i><span>Interactive Map</span></a>
        @auth
        <a href="{{ route('inbox.index') }}" class="nav-item {{ request()->routeIs('inbox.*') ? 'active' : '' }}"><i class="fa-regular fa-paper-plane"></i><span>Inbox</span></a>
        <a href="{{ route('notifications') }}" class="nav-item {{ request()->routeIs('notifications') ? 'active' : '' }}"><i class="fa-regular fa-bell"></i><span>Notifications</span><span class="badge hidden" id="sidebar-badge">0</span></a>
        <a href="{{ route('profile') }}" class="nav-item {{ request()->routeIs('profile') ? 'active' : '' }}"><i class="fa-solid fa-user"></i><span>Profile</span></a>
        <button type="button" class="create-post-btn" data-open-composer><i class="fa-solid fa-pen-nib"></i><span>Create Post</span></button>
        <a href="{{ route('profile') }}" class="user-profile">
            <div class="user-avatar">@if(auth()->user()->avatarSource())<img src="{{ auth()->user()->avatarSource() }}" alt="">@else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}@endif</div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-handle">{{ '@' . auth()->user()->username }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item logout-btn"><i class="fa-solid fa-right-from-bracket"></i><span>Log out</span></button>
        </form>
        @else
        <div class="guest-actions">
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign up</a>
        </div>
        @endauth
    </aside>

    <main class="main-content">
        <div class="top-header">
            <h1>TravelBuddy</h1>
            <form action="{{ route('search') }}" method="GET" class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search destinations, events, posts, people...">
            </form>
            @yield('category-pills')
        </div>
        <div class="content-area">
            @yield('content')
        </div>
    </main>

    @include('partials.sidebar')
</div>

<nav class="bottom-nav">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" aria-label="Home"><i class="fa-solid fa-house"></i></a>
    <a href="{{ route('destinations.index') }}" class="{{ request()->routeIs('destinations.*') ? 'active' : '' }}" aria-label="Destinations"><i class="fa-solid fa-map-location-dot"></i></a>
    <a href="{{ route('community') }}" class="{{ request()->routeIs('community') ? 'active' : '' }}" aria-label="Community"><i class="fa-solid fa-users"></i></a>
    <a href="{{ route('explore') }}" class="{{ request()->routeIs('explore', 'recommendations') ? 'active' : '' }}" aria-label="Explore"><i class="fa-solid fa-compass"></i></a>
    <a href="{{ route('map') }}" class="{{ request()->routeIs('map') ? 'active' : '' }}" aria-label="Map"><i class="fa-solid fa-map"></i></a>
    <a href="{{ auth()->check() ? route('profile') : route('login') }}" class="{{ request()->routeIs('profile') ? 'active' : '' }}" aria-label="Profile"><i class="fa-solid fa-user"></i></a>
</nav>

@include('partials.chatbot')
@auth @include('partials.composer') @endauth
<div class="tb-toast" id="toast"></div>

<script src="{{ asset('js/tb.js') }}"></script>
<script src="{{ asset('js/tb-v2.js') }}"></script>
<script>
@if(session('success')) showToast(@json(session('success')), 'fa-circle-check'); @endif
@if(session('error')) showToast(@json(session('error')), 'fa-circle-exclamation'); @endif
@auth
if (typeof Echo !== 'undefined' && document.querySelector('meta[name="reverb-key"]')) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: document.querySelector('meta[name="reverb-key"]').content,
        wsHost: document.querySelector('meta[name="reverb-host"]').content,
        wsPort: document.querySelector('meta[name="reverb-port"]').content,
        wssPort: document.querySelector('meta[name="reverb-port"]').content,
        forceTLS: (document.querySelector('meta[name="reverb-scheme"]').content ?? 'http') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
    const badge = document.getElementById('sidebar-badge');
    const refreshBadge = () => fetch('{{ route("notifications.unread") }}')
        .then(r => r.json())
        .then(d => { if (!badge) return; badge.textContent = d.count > 99 ? '99+' : d.count; badge.classList.toggle('hidden', d.count === 0); })
        .catch(() => {});
    refreshBadge();
    setInterval(refreshBadge, 30000);
    Echo.private('user.{{ auth()->id() }}').listen('UserNotified', (e) => { showToast(e.message, 'fa-bell'); refreshBadge(); });
}
@endauth
</script>
@stack('scripts')
</body>
</html>