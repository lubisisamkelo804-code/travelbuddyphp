@extends('layouts.app')
@section('title', 'Inbox')

@section('content')
<h1 class="h-display h2">Inbox</h1>
<form method="POST" action="{{ route('inbox.store') }}" class="form-card">
    @csrf
    <div class="grid-2">
        <div class="field"><label>Start a conversation with</label>
            <select class="input" name="user_id" required>
                <option value="">Choose a traveler…</option>
                @foreach($people as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ '@' . $u->username }})</option>
                @endforeach
            </select>
        </div>
        <div class="field"><label>First message (optional)</label>
            <input class="input" type="text" name="body" maxlength="1000" placeholder="Say hello…">
        </div>
    </div>
    <button class="btn btn-primary" type="submit"><i class="fa-regular fa-paper-plane"></i> Send</button>
</form>

<h2 class="section-title">Your conversations</h2>
@forelse($conversations as $c)
    @php $other = $c->otherUser(auth()->user()); $last = $c->lastMessage; @endphp
    <a class="convo-row" href="{{ route('inbox.show', $c) }}">
        <div class="pc-avatar">@if($other && $other->avatarSource())<img src="{{ $other->avatarSource() }}" alt="">@else{{ strtoupper(substr($other->name ?? '?', 0, 1)) }}@endif</div>
        <div class="dc-body">
            <div class="res-name">{{ $other?->name ?? 'Unknown' }}</div>
            <div class="res-sub">@if($last){{ $last->sender_id === auth()->id() ? 'You: ' : '' }}{{ $last->body ?? ('Shared a ' . ($last->share_type ?? 'item')) }}@else No messages yet @endif</div>
        </div>
        <span class="pc-sub">{{ $last?->created_at->diffForHumans() ?? '' }}</span>
    </a>
@empty
    <div class="empty"><h3>No conversations yet</h3><p>Start one above, or share a post from the feed with the Share button.</p></div>
@endforelse
@endsection