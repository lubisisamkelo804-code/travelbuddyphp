@extends('layouts.app')
@section('title', 'My Bookings')
@section('content')
<h1 class="h-display h2">My Bookings</h1>
<div class="feed-col">
    @forelse($bookings as $b)
        @php $target = $b->destination ?? $b->event; $isEvent = (bool) $b->event; @endphp
        <div class="booking-row">
            <div class="ev-datebox"><i class="fa-solid {{ $isEvent ? 'fa-calendar' : 'fa-map-location-dot' }}"></i></div>
            <div class="dc-body">
                <a class="dc-title" href="{{ $isEvent ? route('events.show', $b->event) : route('destinations.show', $b->destination) }}">{{ $target?->name ?? $target?->title ?? 'Removed item' }}</a>
                <div class="dc-sub">{{ $isEvent ? $b->event->start_date->format('d M Y, H:i') : ($b->booked_date?->format('d M Y') ?? 'Flexible dates') }} · {{ $b->guests }} guest(s) · booked {{ $b->created_at->diffForHumans() }}</div>
                <span class="chip {{ $b->status === 'cancelled' ? '' : 'chip-green' }}" style="margin-top:6px">{{ strtoupper($b->status) }}</span>
            </div>
            @if($b->status !== 'cancelled')
                <form method="POST" action="{{ route('bookings.cancel', $b) }}" onsubmit="return confirm('Cancel this booking?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost btn-sm danger" type="submit"><i class="fa-solid fa-xmark"></i> Cancel</button>
                </form>
            @endif
        </div>
    @empty
        <div class="empty">
            @include('partials.logo', ['class' => 'logo-lg', 'id' => 'bk-empty'])
            <h3>No bookings yet</h3>
            <p>Start exploring and book your first trip!</p>
            <a class="btn btn-primary" href="{{ route('destinations.index') }}"><i class="fa-solid fa-compass"></i> Browse destinations</a>
        </div>
    @endforelse
</div>
@endsection