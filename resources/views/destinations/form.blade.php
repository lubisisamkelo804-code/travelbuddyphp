@extends('layouts.app')
@section('title', $destination ? 'Edit Destination' : 'Add Destination')
@section('content')
<div class="form-card form-page">
    <h1 class="h-display h2">{{ $destination ? 'Edit Destination' : 'Add Destination' }}</h1>
    @if($errors->any())<div class="errors"><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" enctype="multipart/form-data" action="{{ $destination ? route('destinations.update', $destination) : route('destinations.store') }}" class="stack-form">
        @csrf
        @if($destination) @method('PUT') @endif
        <div class="grid-2">
            <div class="field"><label>Name *</label><input class="input" type="text" name="name" value="{{ old('name', $destination?->name) }}" required></div>
            <div class="field"><label>City *</label><input class="input" type="text" name="city" value="{{ old('city', $destination?->city) }}" required></div>
        </div>
        <div class="field"><label>Description *</label><textarea class="input" name="description" rows="4" required minlength="10">{{ old('description', $destination?->description) }}</textarea></div>
        <div class="grid-2">
            <div class="field"><label>Estimated cost (R) *</label><input class="input" type="number" step="0.01" min="0" name="estimated_cost" value="{{ old('estimated_cost', $destination?->estimated_cost) }}" required></div>
            <div class="field"><label>Capacity</label><input class="input" type="number" min="1" name="capacity" value="{{ old('capacity', $destination?->capacity) }}"></div>
        </div>
        <div class="grid-2">
            <div class="field"><label>Photo from device</label><input class="input" type="file" name="image" accept="image/*"></div>
            <div class="field"><label>…or image URL</label><input class="input" type="url" name="image_url" value="{{ old('image_url', $destination?->image_url) }}" placeholder="https://…"></div>
        </div>
        <input type="hidden" name="latitude" value="{{ old('latitude', $destination?->latitude) }}">
        <input type="hidden" name="longitude" value="{{ old('longitude', $destination?->longitude) }}">
        @include('partials.map-picker')
        <div class="field"><label>Interests</label>
            <div class="pc-chips">
                @foreach($interests as $i)
                    <label class="chip {{ $destination && $destination->interests->contains($i->id) ? 'chip-on' : '' }}" style="cursor:pointer">
                        <input type="checkbox" name="interests[]" value="{{ $i->id }}" hidden {{ $destination && $destination->interests->contains($i->id) ? 'checked' : '' }}>
                        <i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div class="row-actions">
            <button class="btn btn-primary" type="submit">{{ $destination ? 'Save changes' : 'Add destination' }}</button>
            <a class="btn btn-ghost" href="{{ $destination ? route('destinations.show', $destination) : route('destinations.index') }}">Cancel</a>
        </div>
    </form>
</div>
@endsection