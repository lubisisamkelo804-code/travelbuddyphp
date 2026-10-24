<a class="gcard" href="{{ route('destinations.show', $d) }}">
    <div class="gm">
        @if($d->image_url)<img src="{{ $d->image_url }}" alt="{{ $d->name }}" loading="lazy">@else<div class="no-img" style="width:100%;height:100%"><i class="fa-solid fa-map-location-dot"></i></div>@endif
        @if($d->average_rating > 0)<span class="chip chip-gold gm-chip"><i class="fa-solid fa-star"></i> {{ number_format((float) $d->average_rating, 1) }}</span>@endif
    </div>
    <div class="gb">
        <div class="dc-title">{{ $d->name }}</div>
        <div class="dc-sub"><i class="fa-solid fa-location-dot"></i> {{ $d->city }}</div>
        <div class="dc-price">R{{ number_format($d->estimated_cost, 0) }} <small>/ person</small></div>
    </div>
</a>