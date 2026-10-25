(function () {
'use strict';
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

/* toast */
let tt;
window.showToast = function (msg, icon) {
    icon = icon || 'fa-circle-check';
    let el = $('#toast');
    if (!el) { el = document.createElement('div'); el.id = 'toast'; el.className = 'tb-toast'; document.body.appendChild(el); }
    el.innerHTML = '<i class="fa-solid ' + icon + '"></i><span></span>';
    el.querySelector('span').textContent = msg;
    el.classList.add('show');
    clearTimeout(tt);
    tt = setTimeout(() => el.classList.remove('show'), 2800);
};
window.tbToast = window.showToast;

/* composer sheet */
function openComposer() {
    $('#composer-overlay')?.classList.add('show');
    $('#composer-sheet')?.classList.add('show');
    document.body.style.overflow = 'hidden';
    setTimeout(() => $('#composer-sheet textarea')?.focus(), 250);
}
function closeComposer() {
    $('#composer-overlay')?.classList.remove('show');
    $('#composer-sheet')?.classList.remove('show');
    document.body.style.overflow = '';
}
$$('[data-open-composer]').forEach(b => b.addEventListener('click', e => { e.preventDefault(); openComposer(); }));
$$('[data-close-composer]').forEach(b => b.addEventListener('click', closeComposer));
$('#composer-overlay')?.addEventListener('click', closeComposer);
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeComposer(); if (window.closeBot) window.closeBot(); }
});

/* media upload counter (max 4) */
$$('input[type="file"][name="media[]"]').forEach(inp => {
    inp.addEventListener('change', () => {
        const n = inp.files.length;
        const out = $('#media-count');
        if (out) out.textContent = n ? n + ' file' + (n > 1 ? 's' : '') + ' selected' : '';
        if (n > 4) { showToast('Maximum 4 photos/videos per post', 'fa-circle-exclamation'); inp.value = ''; if (out) out.textContent = ''; }
    });
});

/* swipeable media carousels */
$$('[data-carousel]').forEach(car => {
    const track = $('.mcar-track', car);
    if (!track) return;
    const dots = $$('.mcar-dots .dot', car);
    const step = () => (track.firstElementChild ? track.firstElementChild.clientWidth + 10 : 300);
    $('[data-car-next]', car)?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
    $('[data-car-prev]', car)?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    track.addEventListener('scroll', () => {
        const i = Math.round(track.scrollLeft / step());
        dots.forEach((d, j) => d.classList.toggle('on', j === i));
    }, { passive: true });
});

/* delegated toggles: share / reply / copy */
document.addEventListener('click', e => {
    const sh = e.target.closest('[data-toggle-share]');
    if (sh) { const box = $('#share-' + sh.dataset.toggleShare); if (box) box.hidden = !box.hidden; return; }
    const rp = e.target.closest('[data-toggle-reply]');
    if (rp) { const f = $('#' + rp.dataset.toggleReply); if (f) { f.hidden = !f.hidden; if (!f.hidden) $('input', f)?.focus(); } return; }
    const cp = e.target.closest('[data-copy-link]');
    if (cp) {
        e.preventDefault();
        const url = cp.dataset.copyLink || location.href;
        if (navigator.clipboard) navigator.clipboard.writeText(url).then(() => showToast('Link copied', 'fa-link'));
        else showToast('Could not copy link', 'fa-circle-exclamation');
    }
});

/* interest chips: toggle hidden checkbox + chip-on styling (delegated) */
document.addEventListener('click', e => {
    const inp = e.target.closest('label.chip input[type="checkbox"][name="interests[]"]');
    if (!inp) return;                       // let the browser do the default toggle
    const lab = inp.closest('label');
    requestAnimationFrame(() => lab && lab.classList.toggle('chip-on', inp.checked));
});
document.addEventListener('change', e => {
    const inp = e.target.closest('label.chip input[type="checkbox"][name="interests[]"]');
    if (!inp) return;
    const lab = inp.closest('label');
    if (lab) lab.classList.toggle('chip-on', inp.checked);
});

/* star picker (reviews) */
document.addEventListener('click', e => {
    const star = e.target.closest('[data-star]');
    if (!star) return;
    const picker = star.closest('[data-star-picker]');
    if (!picker) return;
    const val = parseInt(star.dataset.star, 10);
    const input = picker.parentElement.querySelector('input[name="rating"]') || picker.closest('form')?.querySelector('input[name="rating"]');
    if (input) input.value = val;
    $$('[data-star]', picker).forEach(s => s.classList.toggle('on', parseInt(s.dataset.star, 10) <= val));
});
})();