@php $rail = $rail ?? []; @endphp
@if(!empty($rail))
<aside class="right-sidebar" id="right-sidebar">
    @if(!empty($rail['weather']))
    @php $w = $rail['weather']; @endphp
    <div class="rail-card rail-weather">
        <div class="rail-head"><span class="rail-stamp">{{ $w['city'] }}</span><span class="rail-meta">{{ isset($w['offline']) ? 'cached' : 'updated' }} {{ $w['updated'] }}</span></div>
        <div class="rail-wx">
            <span class="rail-wx-icon">{{ $w['icon'] }}</span>
            <div><b class="rail-wx-temp">{{ $w['temp'] }}°</b>
            <div class="rail-wx-label">{{ $w['label'] }} · feels {{ $w['feels'] }}°</div></div>
        </div>
        <div class="rail-stats">
            <div><span>High</span><b>{{ $w['hi'] }}°</b></div>
            <div><span>Low</span><b>{{ $w['lo'] }}°</b></div>
            <div><span>Wind</span><b>{{ $w['wind'] }} km/h</b></div>
        </div>
    </div>
    @endif

    @if(!empty($rail['trending']))
    <div class="rail-card">
        <div class="rail-head"><h3>Trending now</h3><span class="rail-meta">community</span></div>
        <div class="rail-list">
            @foreach($rail['trending'] as $i => $d)
            <a class="rail-row" href="{{ route('destinations.show', $d['id']) }}">
                <span class="rail-num">0{{ $i + 1 }}</span>
                <span class="rail-row-body"><b>{{ $d['name'] }}</b><small>{{ $d['place'] }}</small></span>
                @if($d['price'])<span class="rail-price">R{{ number_format($d['price']) }}</span>@endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($rail['live']))
    <div class="rail-card">
        <div class="rail-head"><h3><span class="rail-pulse"></span> Live from the road</h3></div>
        <div class="rail-list" id="rail-live">
            @foreach($rail['live'] as $a)
            <div class="rail-live-row"><span>{{ $a['icon'] }}</span><div>{!! $a['html'] !!}</div><time>{{ $a['when'] }}</time></div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($rail['events']))
    <div class="rail-card">
        <div class="rail-head"><h3>Happening soon</h3></div>
        <div class="rail-list">
            @foreach($rail['events'] as $e)
            <a class="rail-row" href="{{ route('events.show', $e['id']) }}#book">
                <span class="rail-row-body"><b>{{ $e['title'] }}</b><small>{{ $e['when'] }}{{ $e['place'] ? ' · ' . $e['place'] : '' }}</small></span>
                <span class="btn-mini">Book</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($rail['tip']))
    <div class="rail-card rail-tip">
        <span class="rail-kicker">Field note · {{ now()->format('d M') }}</span>
        <p>"{{ $rail['tip']['text'] }}"</p>
    </div>
    @endif

    @if(!empty($rail['currency']))
    @php $fx = $rail['currency']; $r = $fx['result']; @endphp
    <div class="rail-card">
        <div class="rail-head"><h3>Exchange desk</h3><span class="rail-meta">{{ $fx['base'] }} first</span></div>
        <form method="GET" action="{{ url()->current() }}" class="rail-fx">
            <input type="number" name="amt" min="1" step="0.01" placeholder="1000" value="{{ $r['amount'] ?? '' }}" required>
            <select name="from">@foreach($fx['list'] as $c)<option @selected(($r['from'] ?? $fx['base']) === $c)>{{ $c }}</option>@endforeach</select>
            <span class="rail-fx-arrow">→</span>
            <select name="to">@foreach($fx['list'] as $c)<option @selected(($r['to'] ?? 'USD') === $c)>{{ $c }}</option>@endforeach</select>
            <button class="btn-mini" type="submit">Convert</button>
        </form>
        @if($r)<div class="rail-fx-out"><b>{{ number_format($r['result'], 2) }} {{ $r['to'] }}</b><small>1 {{ $r['from'] }} = {{ $r['rate'] }} {{ $r['to'] }} · {{ $r['live'] ? 'live rate' : 'offline rate' }}</small></div>@endif
    </div>
    @endif

    @if(!empty($rail['photo']))
    <figure class="rail-card rail-polaroid">
        @if($rail['photo']['image'])<img src="{{ $rail['photo']['image'] }}" alt="" loading="lazy" onerror="this.remove()">@endif
        <figcaption><span>{{ $rail['photo']['caption'] }}</span><small>📷 {{ $rail['photo']['author'] }}</small></figcaption>
    </figure>
    @endif

    @if(!empty($rail['people']))
    <div class="rail-card">
        <div class="rail-head"><h3>Travelers to connect</h3><span class="rail-meta">shared interests</span></div>
        <div class="rail-list">
            @foreach($rail['people'] as $p)
            <a class="rail-row" href="{{ route('owners.show', $p['id']) }}">
                <span class="rail-avatar">@if(!empty($p['avatar']))<img src="{{ $p['avatar'] }}" alt="">@else{{ strtoupper(substr($p['name'], 0, 1)) }}@endif</span>
                <span class="rail-row-body"><b>{{ $p['name'] }}</b><small>{{ implode(' · ', array_filter($p['tags'])) }}</small></span>
                <span class="chip chip-green">Say hi</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($rail['quote']))
    <blockquote class="rail-quote">
        <p>{{ $rail['quote']['text'] }}</p>
        <cite>— {{ $rail['quote']['by'] }}</cite>
    </blockquote>
    @endif

    @if(!empty($rail['minimap']))
    <div class="rail-card rail-map-card">
        <div class="rail-head"><h3>On the map</h3><span class="rail-meta">{{ count($rail['minimap']) }} pins</span></div>
        <div class="rail-map" id="rail-map" data-points='@json($rail['minimap'])'></div>
        <a class="rail-link" href="{{ route('map') }}">Open full map →</a>
    </div>
    @endif
</aside>
@endif