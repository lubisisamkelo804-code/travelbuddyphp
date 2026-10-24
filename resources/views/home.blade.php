@extends('layouts.app')
@section('title', 'Home Feed')

@section('category-pills')
<div class="category-pills">
    @foreach(['all' => 'All Posts', 'posts' => 'Posts', 'destinations' => 'Destinations', 'events' => 'Events', 'Food' => 'Food & Culture', 'Adventure' => 'Hiking & Outdoors', 'Beach' => 'Beach', 'Nature' => 'Wildlife & Nature'] as $k => $label)
        <a class="pill {{ $cat === $k ? 'active' : '' }}" href="{{ route('home', ['cat' => $k]) }}">{{ $label }}</a>
    @endforeach
</div>
@endsection

@section('content')
<div class="feed-col">
    @forelse($feed as $row)
        @if($row['kind'] === 'post')
            @include('partials.feed-post', ['post' => $row['item']])
        @elseif($row['kind'] === 'destination')
            @php $d = $row['item']; @endphp
            <a class="dcard" href="{{ route('destinations.show', $d) }}">
                @if($d->image_url)<img src="{{ $d->image_url }}" alt="" loading="lazy">@else<div class="no-img"><i class="fa-solid fa-map-location-dot"></i></div>@endif
                <div class="dc-body">
                    <div class="dc-title">{{ $d->name }}</div>
                    <div class="dc-sub"><i class="fa-solid fa-location-dot"></i> {{ $d->city }} · ★ {{ number_format((float) $d->average_rating, 1) }} ({{ $d->review_count }})</div>
                    <div class="dc-price">R{{ number_format($d->estimated_cost, 0) }} / person</div>
                </div>
            </a>
        @else
            @include('partials.event-card', ['e' => $row['item']])
        @endif
    @empty
        <div class="empty">
            @include('partials.logo', ['class' => 'logo-lg', 'id' => 'home-empty'])
            <h3>Nothing here yet</h3>
            <p>Posts, destinations and events will appear in your feed as soon as they exist.</p>
        </div>
    @endforelse
</div>
@endsection