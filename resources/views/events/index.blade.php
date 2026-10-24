@extends('layouts.app')
@section('title', 'Events')
@section('content')
<div class="community-head">
    <div><div class="eyebrow">Upcoming experiences</div><h1 class="h-display h2">Events</h1></div>
    @auth @if(auth()->user()->isCreator())<a class="btn btn-accent" href="{{ route('events.create') }}"><i class="fa-solid fa-plus"></i> Create Event</a>@endif @endauth
</div>
<form method="GET" action="{{ route('events.index') }}" class="filter-form">
    <input class="input" type="text" name="q" value="{{ request('q') }}" placeholder="Search events…">
    <input class="input" type="text" name="city" value="{{ request('city') }}" placeholder="City">
    <input class="input" type="date" name="from" value="{{ request('from') }}">
    <input class="input" type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max price (R)" min="0">
    <select class="input" name="scope">
        <option value="upcoming" @selected($scope === 'upcoming')>Upcoming</option>
        <option value="past" @selected($scope === 'past')>Past</option>
        <option value="all" @selected($scope === 'all')>All</option>
    </select>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Apply</button>
</form>
<div class="feed-col">
    @forelse($events as $e)
        @include('partials.event-card', ['e' => $e])
    @empty
        <div class="empty"><h3>No events found</h3><p>Try a different search or scope.</p></div>
    @endforelse
</div>
<div class="pager">{{ $events->links() }}</div>
@endsection