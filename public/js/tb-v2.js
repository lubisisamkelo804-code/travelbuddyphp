(function () {
'use strict';
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

/* chatbot (open / CLOSE / send / chips) */
const panel = $('#bot-panel');
const fab = $('#bot-fab');
const box = $('#bot-msgs');
const form = $('#bot-form');
const input = $('#bot-text');
const botUrl = panel?.dataset.url || '/chatbot';
const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

function bubble(html, me) {
    const d = document.createElement('div');
    d.className = 'bot-bubble ' + (me ? 'me' : 'them');
    if (me) d.textContent = html; else d.innerHTML = html;
    box?.appendChild(d);
    if (box) box.scrollTop = box.scrollHeight;
    return d;
}
function openBot() { if (panel) { panel.hidden = false; setTimeout(() => input?.focus(), 200); } }
function closeBot() { if (panel) panel.hidden = true; }
window.closeBot = closeBot;

fab?.addEventListener('click', () => { panel.hidden ? openBot() : closeBot(); });
$('#bot-close')?.addEventListener('click', closeBot);

async function ask(q) {
    if (!q || !box) return;
    bubble(q, true);
    const typing = bubble('Thinking…', false);
    try {
        const res = await fetch(botUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ message: q })
        });
        const data = await res.json();
        typing.remove();
        bubble(data.reply || 'Sorry, I got lost. Try "help".', false);
    } catch (err) {
        typing.remove();
        bubble('I couldn\'t reach the server. Try again in a moment.', false);
    }
}
form?.addEventListener('submit', e => { e.preventDefault(); const q = input.value.trim(); input.value = ''; ask(q); });
$$('.bot-chips button').forEach(b => b.addEventListener('click', () => ask(b.dataset.ask || b.textContent)));

/* rail mini-map (Leaflet) */
const railMap = $('#rail-map');
if (railMap && window.L) {
    try {
        const pts = JSON.parse(railMap.dataset.points || '[]');
        if (pts.length) {
            const m = L.map('rail-map', { scrollWheelZoom: false, zoomControl: false, attributionControl: false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { opacity: .9 }).addTo(m);
            pts.forEach(p => L.circleMarker([p.lat, p.lng], { radius: 6, color: '#c4502a', fillColor: '#e08b5a', fillOpacity: .9 }).addTo(m).bindTooltip(p.name));
            if (pts.length > 1) m.fitBounds(pts.map(p => [p.lat, p.lng]), { padding: [14, 14] });
            else m.setView([pts[0].lat, pts[0].lng], 8);
        }
    } catch (e) { /* rail map is optional */ }
}

/* inbox: scroll chat to newest */
const msgs = $('.chat-msgs');
if (msgs) msgs.scrollTop = msgs.scrollHeight;
})();