@extends('layouts.app')
@section('title', $destination->name)

@section('content')
{{-- Leaflet CSS (self-contained so the map works even if the layout doesn't load it) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    .star-row{display:flex;gap:6px;font-size:28px;color:var(--line-2)}
    .star-row i{cursor:pointer;transition:color .15s,transform .1s}
    .star-row i:hover{transform:scale(1.12);color:var(--gold)}
    .star-row i.on{color:var(--gold)}
    .show-grid{display:grid;grid-template-columns:2fr 1fr;gap:48px}
    @media(max-width:960px){.show-grid{grid-template-columns:1fr}}
</style>

{{-- ============ HERO ============ --}}
<div style="position:relative;height:52vh;min-height:380px;overflow:hidden;background:var(--espresso)">
    @if($destination->image_url)
        <img src="{{ $destination->image_url }}" alt="{{ $destination->name }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85">
    @else
        <div class="placeholder-media" style="position:absolute;inset:0"><i class="fa-solid fa-map-location-dot" style="font-size:120px;opacity:.3"></i></div>
    @endif
    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(34,28,23,.15) 0%,rgba(34,28,23,.78) 100%)"></div>
    <div class="container" style="position:relative;height:100%;display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:48px;color:var(--cream)">
        <div class="eyebrow" style="color:var(--sand)">{{ $destination->city }}{{ $destination->country ? ', ' . $destination->country : '' }}</div>
        <h1 class="h-display h1" style="color:var(--cream);max-width:16ch">{{ $destination->name }}</h1>
        <div class="row" style="margin-top:16px;flex-wrap:wrap;gap:20px;font-size:15px">
            @if($destination->average_rating > 0)
                <span><i class="fa-solid fa-star" style="color:var(--gold)"></i> {{ number_format((float) $destination->average_rating, 1) }} <span style="color:var(--sand)">({{ $destination->review_count }} reviews)</span></span>
            @endif
            <span><i class="fa-regular fa-eye"></i> {{ $destination->view_count }} views</span>
            <span><i class="fa-regular fa-calendar-check"></i> {{ $destination->booking_count }} bookings</span>
        </div>
        @if($destination->interests->count())
            <div class="row" style="margin-top:14px;flex-wrap:wrap;gap:8px">
                @foreach($destination->interests as $i)
                    <span class="chip" style="background:rgba(248,245,240,.15);color:var(--cream);border:1px solid rgba(248,245,240,.25)"><i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<section class="section" style="padding-top:48px">
    <div class="container">
        <div class="row" style="justify-content:space-between;margin-bottom:28px">
            <a href="{{ route('destinations.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to destinations</a>
            @if(auth()->id() === $destination->owner_id)
                <div class="row" style="gap:10px">
                    <a href="{{ route('destinations.edit', $destination) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                    <form method="POST" action="{{ route('destinations.destroy', $destination) }}" onsubmit="return confirm('Delete this destination? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fa-solid fa-trash"></i> Delete</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="show-grid">
            {{-- ============ LEFT COLUMN ============ --}}
            <div>
                <div class="eyebrow">About this place</div>
                <p style="font-size:17px;line-height:1.7;color:var(--ink-2);margin-top:8px;white-space:pre-line">{{ $destination->description }}</p>

                @if($destination->latitude && $destination->longitude)
                    <div style="margin-top:40px">
                        <div class="eyebrow">Location</div>
                        <div id="detail-map" style="height:320px;border-radius:var(--r-lg);margin-top:12px;border:1px solid var(--line);z-index:0"></div>
                    </div>
                @endif

                {{-- ============ REVIEWS ============ --}}
                <div id="reviews" style="margin-top:48px">
                    <div class="spread" style="margin-bottom:24px">
                        <div>
                            <div class="eyebrow">Reviews</div>
                            <h2 class="h-display h3">What travelers say</h2>
                        </div>
                        @if($destination->review_count > 0)
                            <div class="row" style="gap:10px">
                                <span style="font-family:var(--fd);font-size:32px;font-weight:600;color:var(--espresso)">{{ number_format((float) $destination->average_rating, 1) }}</span>
                                <div>
                                    <div style="color:var(--gold);font-size:14px">{{ str_repeat('★', (int) round($destination->average_rating)) }}{{ str_repeat('☆', 5 - (int) round($destination->average_rating)) }}</div>
                                    <div class="muted" style="font-size:13px">{{ $destination->review_count }} reviews</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @auth
                        @if(! $hasReviewed)
                            <form method="POST" action="{{ route('reviews.store') }}" style="background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;margin-bottom:24px">
                                @csrf
                                <input type="hidden" name="reviewable_type" value="destination">
                                <input type="hidden" name="reviewable_id" value="{{ $destination->id }}">
                                <input type="hidden" name="rating" id="rating-input" value="0">
                                <div class="field" style="margin-bottom:16px">
                                    <label>Your rating</label>
                                    <div class="star-row" id="star-row">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fa-solid fa-star" data-star="{{ $s }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <div class="field">
                                    <label>Your review</label>
                                    <textarea name="body" rows="3" required minlength="5" class="textarea" placeholder="Share your experience...">{{ old('body') }}</textarea>
                                </div>
                                <button class="btn btn-primary" style="margin-top:16px"><i class="fa-solid fa-paper-plane"></i> Submit review</button>
                            </form>
                        @else
                            <div style="background:var(--green-soft);border:1px solid var(--green);border-radius:var(--r-md);padding:14px 18px;margin-bottom:24px;font-size:14px;color:var(--green);font-weight:600">
                                <i class="fa-solid fa-circle-check"></i> You've already reviewed this destination.
                            </div>
                        @endif
                    @else
                        <div style="background:var(--cream);border-radius:var(--r-md);padding:18px;margin-bottom:24px;font-size:14px">
                            <a href="{{ route('login') }}" style="color:var(--accent);font-weight:700">Log in</a> to write a review.
                        </div>
                    @endauth

                    <div style="display:flex;flex-direction:column;gap:20px">
                        @forelse($destination->reviews as $rev)
                            <div style="background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:22px">
                                <div class="row" style="margin-bottom:12px">
                                    <div class="avatar" style="width:42px;height:42px;font-size:15px">{{ strtoupper(substr($rev->author->name, 0, 1)) }}</div>
                                    <div style="flex:1">
                                        <div style="font-weight:700;font-size:15px">{{ $rev->author->name }}</div>
                                        <div class="muted" style="font-size:13px">{{ $rev->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div style="color:var(--gold);font-size:14px">{{ str_repeat('★', (int) $rev->rating) }}</div>
                                </div>
                                <p style="font-size:15px;line-height:1.6;color:var(--ink-2)">{{ $rev->body }}</p>
                                @if(auth()->id() === $rev->author_id)
                                    <form method="POST" action="{{ route('reviews.destroy', $rev) }}" onsubmit="return confirm('Delete your review?')" style="margin-top:12px">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-ghost btn-sm" style="color:var(--danger);padding:6px 12px;font-size:12px"><i class="fa-solid fa-trash"></i> Delete</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <div class="empty" style="padding:48px 24px">
                                <i class="fa-regular fa-comment" style="font-size:40px;opacity:.3"></i>
                                <h3>No reviews yet</h3>
                                <p>Be the first to share your experience!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ============ RIGHT COLUMN (BOOKING) ============ --}}
            <aside>
                <div style="position:sticky;top:calc(var(--nav-h) + 24px);background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:28px;box-shadow:var(--sh-1)">
                    <div class="price" style="font-size:28px">R{{ number_format($destination->estimated_cost, 0) }} <small style="font-size:13px">/ person</small></div>

                    @if($myBooking)
                        <div style="background:var(--green-soft);border:1px solid var(--green);border-radius:var(--r-md);padding:14px;margin-top:20px;font-size:14px;color:var(--green);font-weight:700">
                            <i class="fa-solid fa-circle-check"></i> Booking confirmed · {{ $myBooking->guests }} guest(s)
                        </div>
                        <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-block" style="margin-top:12px">View my bookings</a>
                    @elseif(auth()->check())
                        <form method="POST" action="{{ route('destinations.book', $destination) }}" style="margin-top:20px;display:flex;flex-direction:column;gap:14px">
                            @csrf
                            <div class="field">
                                <label>Visit date</label>
                                <input type="date" name="booked_date" class="input">
                            </div>
                            <div class="field">
                                <label>Guests</label>
                                <input type="number" name="guests" value="1" min="1" max="20" class="input">
                            </div>
                            <div class="field">
                                <label>Notes (optional)</label>
                                <textarea name="notes" rows="2" class="textarea" placeholder="Any special requests?"></textarea>
                            </div>
                            <button class="btn btn-accent btn-block"><i class="fa-solid fa-calendar-check"></i> Book this destination</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-accent btn-block" style="margin-top:20px">Log in to book</a>
                    @endif

                    @if($destination->capacity)
                        <div class="row" style="margin-top:16px;padding-top:16px;border-top:1px solid var(--line);font-size:13px;color:var(--muted)">
                            <i class="fa-solid fa-users"></i>
                            <span>Capacity: {{ $destination->booking_count }} / {{ $destination->capacity }} booked</span>
                        </div>
                    @endif

                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--line)">
                        <div class="eyebrow">Hosted by</div>
                        <div class="row" style="margin-top:10px">
                            <div class="avatar" style="width:44px;height:44px">{{ strtoupper(substr($destination->owner->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700;font-size:15px">{{ $destination->owner->name }}</div>
                                <div class="muted" style="font-size:13px">{{ '@' . $destination->owner->username }} · Destination Owner</div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    /* ---- Star picker ---- */
    var row = document.getElementById('star-row');
    var input = document.getElementById('rating-input');
    if (row && input) {
        row.querySelectorAll('i').forEach(function (star) {
            star.addEventListener('click', function () {
                var val = parseInt(star.dataset.star, 10);
                input.value = val;
                row.querySelectorAll('i').forEach(function (s) {
                    s.classList.toggle('on', parseInt(s.dataset.star, 10) <= val);
                });
            });
        });
    }
    /* ---- Detail map ---- */
    @if($destination->latitude && $destination->longitude)
    if (typeof L !== 'undefined' && document.getElementById('detail-map')) {
        var m = L.map('detail-map').setView([{{ $destination->latitude }}, {{ $destination->longitude }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(m);
        L.marker([{{ $destination->latitude }}, {{ $destination->longitude }}]).addTo(m).bindPopup('<b>{{ $destination->name }}</b>').openPopup();
    }
    @endif
})();
</script>
@endpush
@endsection