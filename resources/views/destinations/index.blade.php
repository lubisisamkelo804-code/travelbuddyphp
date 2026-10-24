@extends('layouts.app')
@section('title', 'Destinations')
@section('content')
<div class="community-head">
    <div><div class="eyebrow">Explore the world</div><h1 class="h-display h2">Destinations</h1></div>
    @auth @if(auth()->user()->isOwner())<a class="btn btn-accent" href="{{ route('destinations.create') }}"><i class="fa-solid fa-plus"></i> Add Destination</a>@endif @endauth
</div>
<form method="GET" action="{{ route('destinations.index') }}" class="filter-form">
    <input class="input" type="text" name="q" value="{{ request('q') }}" placeholder="Search destinations…">
    <input class="input" type="text" name="city" value="{{ request('city') }}" placeholder="City">
    <input class="input" type="number" name="max_cost" value="{{ request('max_cost') }}" placeholder="Max price (R)" min="0">
    <select class="input" name="sort">
        @foreach(['newest' => 'Newest', 'rating' => 'Highest rated', 'cost_low' => 'Price: low → high', 'cost_high' => 'Price: high → low', 'popular' => 'Most popular'] as $k => $label)
            <option value="{{ $k }}" @selected(request('sort', 'newest') === $k)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Apply</button>
    <div class="filter-chips">
        @foreach($interests as $i)
            <label class="chip {{ in_array($i->id, (array) request('interests', [])) ? 'chip-on' : '' }}" style="cursor:pointer">
                <input type="checkbox" name="interests[]" value="{{ $i->id }}" hidden {{ in_array($i->id, (array) request('interests', [])) ? 'checked' : '' }} onchange="this.form.requestSubmit()">
                <i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}
            </label>
        @endforeach
    </div>
</form>
<div class="grid-cards">
    @forelse($destinations as $d)
        @include('partials.destination-card', ['d' => $d])
    @empty
        <div class="empty" style="grid-column:1/-1"><h3>No destinations found</h3><p>Try adjusting your search or filters.</p></div>
    @endforelse
</div>
<div class="pager">{{ $destinations->links() }}</div>
@endsection