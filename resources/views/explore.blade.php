@extends('layouts.app')
@section('title', 'Explore · For You')
@section('content')
@if($personalized)
    <p class="explore-note"><i class="fa-solid fa-wand-magic-sparkles"></i> Picked for you from places you've visited, booked and liked.</p>
@else
    <p class="explore-note gold"><i class="fa-solid fa-lightbulb"></i> <a href="{{ route('profile') }}">Set your interests</a> to personalise Explore.</p>
@endif
<h2 class="section-title">From destination owners</h2>
<div class="feed-col">
    @forelse($businessPosts as $post)
        @include('partials.feed-post', ['post' => $post])
    @empty <p class="muted-sm">No official business posts yet.</p> @endforelse
</div>
<h2 class="section-title">Places you may love</h2>
<div class="grid-cards">
    @forelse($destinations as $d)
        @include('partials.destination-card', ['d' => $d])
    @empty <p class="muted-sm">No destinations yet.</p> @endforelse
</div>
<h2 class="section-title">Events worth going to</h2>
<div class="feed-col">
    @forelse($events as $e)
        @include('partials.event-card', ['e' => $e])
    @empty <p class="muted-sm">No upcoming events match yet.</p> @endforelse
</div>
@endsection