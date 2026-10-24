<div class="map-picker">
    <label class="field-label">Pick location on map</label>
    <input type="text" id="geo-search" class="input" placeholder="Search address or city, then press Enter…">
    <div id="picker-map" class="picker-map"></div>
    <p class="muted-sm">Click the map or drag the pin — coordinates fill in automatically. Geocoding by OpenStreetMap Nominatim.</p>
</div>
@push('scripts')
<script>
(function () {
    var latIn = document.querySelector('input[name="latitude"]');
    var lngIn = document.querySelector('input[name="longitude"]');
    var el = document.getElementById('picker-map');
    if (!latIn || !lngIn || !el || typeof L === 'undefined') return;
    var has = latIn.value && lngIn.value;
    var map = L.map('picker-map').setView(has ? [parseFloat(latIn.value), parseFloat(lngIn.value)] : [-29.0, 24.0], has ? 10 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    setTimeout(function () { map.invalidateSize(); }, 200);
    var pin = null;
    function setPin(lat, lng, zoom) {
        if (!pin) {
            pin = L.marker([lat, lng], { draggable: true }).addTo(map);
            pin.on('dragend', function () { var p = pin.getLatLng(); latIn.value = p.lat.toFixed(7); lngIn.value = p.lng.toFixed(7); });
        } else { pin.setLatLng([lat, lng]); }
        latIn.value = lat.toFixed(7); lngIn.value = lng.toFixed(7);
        if (zoom) map.setView([lat, lng], zoom);
    }
    if (has) setPin(parseFloat(latIn.value), parseFloat(lngIn.value));
    map.on('click', function (e) { setPin(e.latlng.lat, e.latlng.lng); });
    document.getElementById('geo-search').addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        var q = e.target.value.trim();
        if (!q) return;
        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (rows) { if (rows.length) setPin(parseFloat(rows[0].lat), parseFloat(rows[0].lon), 12); else if (window.showToast) showToast('Address not found', 'fa-circle-exclamation'); })
            .catch(function () { if (window.showToast) showToast('Geocoding service unreachable', 'fa-circle-exclamation'); });
    });
})();
</script>
@endpush