@extends('layouts.app')
@section('title', 'Chat')
@section('content')
@php $me = auth()->user(); $other = $conversation->otherUser($me); @endphp
<div class="chat-wrap">
    <div class="chat-head">
        <a href="{{ route('inbox.index') }}" class="icon-btn-sm" aria-label="Back"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="pc-avatar">@if($other && $other->avatarSource())<img src="{{ $other->avatarSource() }}" alt="">@else{{ strtoupper(substr($other->name ?? '?', 0, 1)) }}@endif</div>
        <div>
            <a href="{{ $other ? route('owners.show', $other) : '#' }}" class="pc-name">{{ $other?->name ?? 'Unknown' }}</a>
            <div class="pc-sub">{{ '@' . ($other->username ?? 'unknown') }}</div>
        </div>
    </div>
    <div class="chat-msgs">
        @foreach($conversation->messages as $m)
            @php $item = $m->share_type ? $m->shareItem() : null; @endphp
            <div class="bub {{ $m->sender_id === $me->id ? 'me' : 'them' }}">
                @if($m->body){{ $m->body }}@endif
                @if($item)
                    @php
                        $link = match($m->share_type) {
                            'post' => route('posts.show', $item),
                            'destination' => route('destinations.show', $item),
                            'event' => route('events.show', $item),
                        };
                        $label = match($m->share_type) {
                            'post' => Str::limit($item->content, 60),
                            'destination' => $item->name,
                            'event' => $item->title,
                        };
                    @endphp
                    <a class="share-card" href="{{ $link }}"><i class="fa-solid fa-share-nodes"></i> {{ $label }}</a>
                @endif
                <div class="bub-time">{{ $m->created_at->diffForHumans() }}</div>
            </div>
        @endforeach
    </div>
    <form method="POST" action="{{ route('inbox.send', $conversation) }}" class="chat-form">
        @csrf
        <input type="text" name="body" maxlength="1000" placeholder="Write a message…" required>
        <button class="btn-mini" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>
@endsection