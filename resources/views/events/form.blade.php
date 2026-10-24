@extends('layouts.app')
@section('title', $event ? 'Edit Event' : 'Create Event')
@section('content')
<div class="form-card form-page">
    <h1 class="h-display h2">{{ $event ? 'Edit Event' : 'Create Event' }}</h1>
    @if($errors->any())<div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ $event ? route('events.update', $event) : route('events.store') }}" class="stack-form">
        @csrf
        @if($event) @method('PUT') @endif
        <div class="field"><label>Title *</label><input class="input" type="text" name="title" value="{{ old('title', $event?->title) }}" required></div>
        <div class="field"><label>Description *</label><textarea class="input" name="description" rows="4" required minlength="10">{{ old('description', $event?->description) }}</textarea></div>
        <div class="grid-2">
            <div class="field"><label>Venue *</label><input class="input" type="text" name="venue" value="{{ old('venue', $event?->venue) }}" required></div>
            <div class="field"><label>City *</label><input class="input" type="text" name="city" value="{{ old('city', $event?->city) }}" required></div>
        </div>
        <div class="grid-2">
            <div class="field"><label>Starts *</label><input class="input" type="datetime-local" name="start_date" value="{{ old('start_date', $event?->start_date?->format('Y-m-d\TH:i')) }}" required></div>
            <div class="field"><label>Ends *</label><input class="input" type="datetime-local" name="end_date" value="{{ old('end_date', $event?->end_date?->format('Y-m-d\TH:i')) }}" required></div>
        </div>
        <div class="grid-2">
            <div class="field"><label>Booking end date</label><input class="input" type="datetime-local" name="booking_end_date" value="{{ old('booking_end_date', $event?->booking_end_date?->format('Y-m-d\TH:i')) }}"></div>
            <div class="field"><label>External booking URL (optional)</label><input class="input" type="url" name="external_booking_url" value="{{ old('external_booking_url', $event?->external_booking_url) }}" placeholder="https://…"></div>
        </div>
        <div class="grid-2">
            <div class="field"><label>Price (R)</label><input class="input" type="number" step="0.01" min="0" name="price" value="{{ old('price', $event?->price ?? 0) }}"></div>
            <div class="field"><label>Capacity</label><input class="input" type="number" min="1" name="capacity" value="{{ old('capacity', $event?->capacity) }}"></div>
        </div>
        <div class="field"><label>Destination (optional)</label>
            <select class="input" name="destination_id">
                <option value="">— None —</option>
                @foreach($destinations as $d)<option value="{{ $d->id }}" @selected(old('destination_id', $event?->destination_id) == $d->id)>{{ $d->name }}</option>@endforeach
            </select>
        </div>
        <div class="grid-2">
            <div class="field"><label>Photo from device</label><input class="input" type="file" name="image" accept="image/*"></div>
            <div class="field"><label>…or image URL</label><input class="input" type="url" name="image_url" value="{{ old('image_url', $event?->image_url) }}"></div>
        </div>
        <input type="hidden" name="latitude" value="{{ old('latitude', $event?->latitude) }}">
        <input type="hidden" name="longitude" value="{{ old('longitude', $event?->longitude) }}">
        @include('partials.map-picker')
        <div class="field"><label>Interests</label>
            <div class="pc-chips">
                @foreach($interests as $i)
                    <label class="chip {{ $event && $event->interests->contains($i->id) ? 'chip-on' : '' }}" style="cursor:pointer">
                        <input type="checkbox" name="interests[]" value="{{ $i->id }}" hidden {{ $event && $event->interests->contains($i->id) ? 'checked' : '' }}>
                        <i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="row-actions">
            <button class="btn btn-primary" type="submit">{{ $event ? 'Save changes' : 'Create event' }}</button>
            <a class="btn btn-ghost" href="{{ $event ? route('events.show', $event) : route('events.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection