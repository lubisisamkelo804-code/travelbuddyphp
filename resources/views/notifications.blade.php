@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="community-head">
    <h1 class="h-display h2">Notifications</h1>
    <form method="POST" action="{{ route('notifications.read') }}">
        @csrf
        <button class="btn btn-ghost btn-sm" type="submit"><i class="fa-solid fa-check-double"></i> Mark all read</button>
    </form>
</div>
<div class="feed-col">
    @forelse($notifications as $n)
        <div class="notif-row {{ $n->is_read ? 'read' : '' }}">
            <span class="res-ico"><i class="fa-regular fa-bell"></i></span>
            <div class="dc-body">
                @if($n->link)<a class="res-name" href="{{ $n->link }}">{{ $n->message }}</a>@else<div class="res-name">{{ $n->message }}</div>@endif
                <div class="res-sub">{{ $n->created_at->diffForHumans() }}</div>
            </div>
            @unless($n->is_read)<span class="unread-dot"></span>@endunless
        </div>
    @empty
        <div class="empty"><h3>No notifications</h3><p>Bookings, reviews and comments will appear here in real time.</p></div>
    @endforelse
</div>
<div class="pager">{{ $notifications->links() }}</div>
@endsection