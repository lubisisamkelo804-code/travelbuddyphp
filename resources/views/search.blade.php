@extends('layouts.app')
@section('title', 'Search')
@section('content')
<form method="GET" action="{{ route('search') }}" class="search-form">
    <input class="input" type="text" name="q" value="{{ $q }}" placeholder="Search anything on TravelBuddy…" autofocus>
    <select class="input" name="category">
        @foreach(['all' => 'Everything', 'destinations' => 'Destinations', 'events' => 'Events', 'posts' => 'Posts', 'people' => 'People'] as $k => $label)
            <option value="{{ $k }}" @selected($cat === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
</form>
@if($q !== '')
    @if($none)
        <div class="not-found">
            <i class="fa-solid fa-magnifying-glass-minus"></i>
            <h3>"{{ $q }}" does not exist on TravelBuddy yet.</h3>
            <p>Try a different keyword or switch the category.</p>
        </div>
    @else
        @if($results['destinations']->count())
            <h2 class="section-title">Destinations</h2>
            @foreach($results['destinations'] as $d)
                <a class="res-row" href="{{ route('destinations.show', $d) }}"><div class="res-ico"><i class="fa-solid fa-location-dot"></i></div><div><div class="res-name">{{ $d->name }}</div><div class="res-sub">{{ $d->city }} · ★ {{ number_format((float) $d->average_rating, 1) }}</div></div></a>
            @endforeach
        @endif
        @if($results['events']->count())
            <h2 class="section-title">Events</h2>
            @foreach($results['events'] as $e)
                <a class="res-row" href="{{ route('events.show', $e) }}#book"><div class="res-ico"><i class="fa-regular fa-calendar"></i></div><div><div class="res-name">{{ $e->title }}</div><div class="res-sub">{{ $e->city }} · {{ $e->start_date->format('d M Y') }}</div></div></a>
            @endforeach
        @endif
        @if($results['posts']->count())
            <h2 class="section-title">Posts</h2>
            @foreach($results['posts'] as $p)
                <a class="res-row" href="{{ route('posts.show', $p) }}"><div class="res-ico"><i class="fa-regular fa-pen-to-square"></i></div><div><div class="res-name">{{ $p->author?->name }}</div><div class="res-sub">{{ Str::limit($p->content, 70) }}</div></div></a>
            @endforeach
        @endif
        @if($results['people']->count())
            <h2 class="section-title">People</h2>
            @foreach($results['people'] as $u)
                <div class="res-row" style="display:flex;align-items:center">
                    <a href="{{ route('owners.show', $u) }}" style="display:flex;align-items:center;gap:10px;color:inherit;text-decoration:none;min-width:0">
                        <div class="res-ico">@if($u->avatarSource())<img src="{{ $u->avatarSource() }}" alt="">@else<i class="fa-regular fa-user"></i>@endif</div>
                        <div><div class="res-name">{{ $u->name }}</div><div class="res-sub">{{ '@' . $u->username }} · {{ ucfirst(str_replace('_', ' ', $u->role)) }}</div></div>
                    </a>
                    @auth
                        @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('inbox.start', $u) }}">
                                @csrf
                                <button class="btn-mini" type="submit"><i class="fa-regular fa-paper-plane"></i> Message</button>
                            </form>
                        @endif
                    @endauth
                </div>
            @endforeach
        @endif
    @endif
@endif
@endsection