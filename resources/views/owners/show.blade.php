@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="owner-head">
    <div class="owner-avatar">@if($user->avatarSource())<img src="{{ $user->avatarSource() }}" alt="">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif</div>
    <div class="owner-body">
        <h1 class="dc-title" style="font-size:26px">{{ $user->name }}</h1>
        <div class="pc-sub">{{ '@' . $user->username }} · {{ ucfirst(str_replace('_', ' ', $user->role)) }} · {{ $user->rank }}</div>
        @if($user->bio)<p class="owner-bio">{{ $user->bio }}</p>@endif
        <div class="social-row">
            @if($user->website)<a href="{{ $user->website }}" target="_blank" rel="noopener" title="Website"><i class="fa-solid fa-globe"></i></a>@endif
            @if($user->social_instagram)<a href="{{ $user->social_instagram }}" target="_blank" rel="noopener" title="Instagram"><i class="fa-brands fa-instagram"></i></a>@endif
            @if($user->social_facebook)<a href="{{ $user->social_facebook }}" target="_blank" rel="noopener" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>@endif
            @if($user->social_x)<a href="{{ $user->social_x }}" target="_blank" rel="noopener" title="X"><i class="fa-brands fa-x-twitter"></i></a>@endif
        </div>
        @if($user->interests->count())
            <div class="pc-chips" style="margin-top:12px">@foreach($user->interests as $i)<span class="chip"><i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}</span>@endforeach</div>
        @endif
    </div>
</div>
<h2 class="section-title">All posts ({{ $posts->total() }})</h2>
<div class="feed-col">
    @forelse($posts as $post)
        @include('partials.feed-post', ['post' => $post])
    @empty
        <div class="empty"><h3>No posts yet</h3><p>When {{ $user->name }} shares something, it will appear here.</p></div>
    @endforelse
</div>
<div class="pager">{{ $posts->links() }}</div>
@if($destinations->count())
    <h2 class="section-title">Their destinations</h2>
    <div class="grid-cards">@foreach($destinations as $d)@include('partials.destination-card', ['d' => $d])@endforeach</div>
@endif
@endsection