<a class="ecard" href="{{ route('events.show', $e) }}#book">
    <div class="ev-datebox"><b>{{ $e->start_date->format('d') }}</b><span>{{ strtoupper($e->start_date->format('M')) }}</span></div>
    <div class="dc-body">
        <div class="dc-title">{{ $e->title }}</div>
        <div class="dc-sub"><i class="fa-solid fa-location-dot"></i> {{ $e->city }} · {{ $e->start_date->format('d M, H:i') }}</div>
        <div class="dc-price">{{ $e->price > 0 ? 'R' . number_format($e->price, 0) : 'Free' }} · {{ $e->booking_count }} going</div>
    </div>
</a>