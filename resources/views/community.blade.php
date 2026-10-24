@extends('layouts.app')
@section('title', 'Community')
@section('content')
<div class="community-head">
    <div>
        <div class="eyebrow">Share your journey</div>
        <h1 class="h-display h2">Community Feed</h1>
        <p class="sub">Adventure logs & official business posts from travelers around the world.</p>
    </div>
    @auth <button type="button" class="btn btn-accent" data-open-composer><i class="fa-solid fa-pen-nib"></i> Share your adventure</button> @endauth
</div>
<div class="feed-col">
    @forelse($posts as $post)
        @include('partials.feed-post', ['post' => $post, 'withThread' => true])
    @empty
        <div class="empty">
            @include('partials.logo', ['class' => 'logo-lg', 'id' => 'com-empty'])
            <h3>No travel logs yet</h3>
            <p>Adventure Seekers' posts about places they've visited will show up here.</p>
        </div>
    @endforelse
</div>
<div class="pager">{{ $posts->links() }}</div>
@endsection