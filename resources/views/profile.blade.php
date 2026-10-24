@extends('layouts.app')
@section('title', 'Profile')
@section('content')
<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="form-card">
    @csrf @method('PUT')
    <div class="profile-top-row">
        <div class="avatar-preview">@if($user->avatarSource())<img src="{{ $user->avatarSource() }}" alt="">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif</div>
        <div class="profile-id">
            <h1 class="dc-title" style="font-size:24px">{{ $user->name }}</h1>
            <div class="pc-sub">{{ '@' . $user->username }} · {{ ucfirst(str_replace('_', ' ', $user->role)) }} · {{ $user->rank }} ({{ $user->review_points }} pts)</div>
            <label class="file-btn" style="margin-top:12px"><i class="fa-solid fa-camera"></i> Upload profile picture
                <input type="file" name="avatar" accept="image/*" hidden>
            </label>
            <span class="muted-sm" style="margin-left:8px">JPG/PNG/WebP from your device — no URLs needed</span>
        </div>
    </div>
    <div class="grid-2">
        <div class="field"><label>Name</label><input class="input" type="text" name="name" value="{{ old('name', $user->name) }}" required></div>
        <div class="field"><label>Budget level</label>
            <select class="input" name="budget_level">
                <option value="1" @selected($user->budget_level === 1)>Budget</option>
                <option value="2" @selected($user->budget_level === 2)>Mid-range</option>
                <option value="3" @selected($user->budget_level === 3)>Luxury</option>
            </select>
        </div>
    </div>
    <div class="field"><label>Bio</label><textarea class="input" name="bio" rows="3" maxlength="500">{{ old('bio', $user->bio) }}</textarea></div>
    <div class="grid-2">
        <div class="field"><label>Website</label><input class="input" type="url" name="website" value="{{ old('website', $user->website) }}"></div>
        <div class="field"><label>Instagram</label><input class="input" type="url" name="social_instagram" value="{{ old('social_instagram', $user->social_instagram) }}"></div>
        <div class="field"><label>Facebook</label><input class="input" type="url" name="social_facebook" value="{{ old('social_facebook', $user->social_facebook) }}"></div>
        <div class="field"><label>X / Twitter</label><input class="input" type="url" name="social_x" value="{{ old('social_x', $user->social_x) }}"></div>
    </div>
    <div class="field"><label>Interests</label>
        <div class="pc-chips">
            @foreach($interests as $i)
                <label class="chip {{ $user->interests->contains($i->id) ? 'chip-on' : '' }}" style="cursor:pointer">
                    <input type="checkbox" name="interests[]" value="{{ $i->id }}" hidden {{ $user->interests->contains($i->id) ? 'checked' : '' }}>
                    <i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}
                </label>
            @endforeach
        </div>
    </div>
    <div class="row-actions">
        <button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save profile</button>
        <a class="btn btn-ghost" href="{{ route('owners.show', $user) }}"><i class="fa-regular fa-eye"></i> View public page</a>
        <a class="btn btn-ghost" href="{{ route('bookings.index') }}"><i class="fa-regular fa-calendar-check"></i> My bookings</a>
    </div>
</form>
<h2 class="section-title">My posts</h2>
<div class="feed-col">
    @forelse($user->posts as $post)
        @include('partials.feed-post', ['post' => $post])
    @empty <p class="muted-sm">You haven't posted yet — use <b>Create Post</b> in the sidebar.</p> @endforelse
</div>
@if($myDestinations->count())
    <h2 class="section-title">My destinations</h2>
    <div class="grid-cards">@foreach($myDestinations as $d)@include('partials.destination-card', ['d' => $d])@endforeach</div>
@endif
@if($myEvents->count())
    <h2 class="section-title">My events</h2>
    <div class="feed-col">@foreach($myEvents as $e)@include('partials.event-card', ['e' => $e])@endforeach</div>
@endif
@endsection