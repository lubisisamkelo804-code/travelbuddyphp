@extends('layouts.app')
@section('title', 'Explore Map')
@section('content')
@php
    $destPoints = $destinations->values()->map(function ($d) {
        return ['kind' => 'destination', 'id' => $d->id, 'name' => $d->name, 'city' => $d->city,
                'lat' => (float) $d->latitude, 'lng' => (float) $d->longitude,
                'url' => route('owners.show', $d->owner_id)];
    });
    $eventPoints = $events->values()->map(function ($e) {
        return ['kind' => 'event', 'id' => $e->id, 'name' => $e->title, 'city' => $e->city,
                'lat' => (float) $e->latitude, 'lng' => (float) $e->longitude,
                'url' => route('events.show', $e->id) . '#book'];
    });
@endphp
<div class="map-toolbar">
    <h1 class="h-display h2">Explore Map</h1>
    <input id="map-filter" type="text" class="input" placeholder="Filter pins by name…">
    <label class="check"><input type="checkbox" id="show-dest" checked> Destinations</label>
    <label class="check"><input type="checkbox" id="show-ev" checked> Events</label>
    <span class="live-note"><i class="fa-solid fa-tower-broadcast"></i> Live — new pins appear in real time</span>
</div>
<p class="muted-sm map-hint"><i class="fa-solid fa-circle-info"></i> Click a green pin to visit the <b>owner's page</b> · click a gold pin to go straight to <b>booking</b>.</p>
<div class="map-shell"><div id="explore-map"></div></div>
@push('scripts')
<script>
    var dests = @json($destPoints);
    var evs = @json($eventPoints);
    var all = dests.concat(evs);
    var map = L.map('explore-map').setView([-29.0, 24.0], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    function iconFor(kind) {
        var color = kind === 'destination' ? '#2f5d50' : '#dd9a2b';
        var fa = kind === 'destination' ? 'fa-map-pin' : 'fa-calendar';
        return L.divIcon({ className: '', iconSize: [32, 32], iconAnchor: [16, 32],
            html: '<div style="background:' + color + ';width:32px;height:32px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center"><i class="fa-solid ' + fa + '" style="transform:rotate(45deg);color:#fff;font-size:13px"></i></div>' });
    }
    var layer = L.layerGroup().addTo(map);
    function redraw() {
        layer.clearLayers();
        var q = document.getElementById('map-filter').value.toLowerCase();
        var showD = document.getElementById('show-dest').checked;
        var showE = document.getElementById('show-ev').checked;
        var pts = [];
        all.forEach(function (p) {
            if (q && p.name.toLowerCase().indexOf(q) === -1 && p.city.toLowerCase().indexOf(q) === -1) return;
            if (p.kind === 'destination' && !showD) return;
            if (p.kind === 'event' && !showE) return;
            var cta = p.kind === 'destination' ? "View owner's page →" : 'Book this event →';
            layer.addLayer(L.marker([p.lat, p.lng], { icon: iconFor(p.kind) })
                .bindPopup('<b>' + p.name + '</b><br><span style="color:#8a7f73">' + p.city + '</span><br><a href="' + p.url + '" style="color:#c4502a;font-weight:700">' + cta + '</a>'));
            pts.push([p.lat, p.lng]);
        });
        if (pts.length > 1) map.fitBounds(pts, { padding: [50, 50] });
        else if (pts.length === 1) map.setView(pts[0], 11);
    }
    redraw();
    document.getElementById('map-filter').addEventListener('input', redraw);
    document.getElementById('show-dest').addEventListener('change', redraw);
    document.getElementById('show-ev').addEventListener('change', redraw);
    @auth
    if (window.Echo) {
        Echo.channel('map').listen('PlacePublished', function (p) {
            all.push(p);
            redraw();
            showToast('New on the map: ' + p.name + ' 📍', 'fa-map-location-dot');
        });
    }
    @endauth
</script>
@endpush
@endsection