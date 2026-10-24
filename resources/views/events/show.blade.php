@extends('layouts.app')
@section('title', $event->title)

@section('content')
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
    @if($event->image_url)
        <img src="{{ $event->image_url }}" alt="{{ $event->title }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.85">
    @else
        <div class="placeholder-media" style="position:absolute;inset:0"><i class="fa-regular fa-calendar" style="font-size:120px;opacity:.3"></i></div>
    @endif
    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(34,28,23,.15) 0%,rgba(34,28,23,.78) 100%)"></div>
    <div class="container" style="position:relative;height:100%;display:flex;flex-direction:column;justify-content:flex-end;padding-bottom:48px;color:var(--cream)">
        <div class="eyebrow" style="color:var(--sand)">{{ $event->city }}</div>
        <h1 class="h-display h1" style="color:var(--cream);max-width:16ch">{{ $event->title }}</h1>
        <div class="row" style="margin-top:16px;flex-wrap:wrap;gap:20px;font-size:15px">
            <span><i class="fa-regular fa-calendar"></i> {{ $event->start_date->format('d M Y, H:i') }} → {{ $event->end_date->format('d M Y, H:i') }}</span>
            <span><i class="fa-solid fa-location-dot"></i> {{ $event->venue }}</span>
            @if($event->average_rating > 0)
                <span><i class="fa-solid fa-star" style="color:var(--gold)"></i> {{ number_format((float) $event->average_rating, 1) }} <span style="color:var(--sand)">({{ $event->review_count }} reviews)</span></span>
            @endif
            <span><i class="fa-solid fa-user-group"></i> {{ $event->booking_count }} attending</span>
            @if($event->isPast())<span style="color:#ffb4c2;font-weight:700"><i class="fa-solid fa-clock-rotate-left"></i> Past event</span>@endif
        </div>
        @if($event->interests->count())
            <div class="row" style="margin-top:14px;flex-wrap:wrap;gap:8px">
                @foreach($event->interests as $i)
                    <span class="chip" style="background:rgba(248,245,240,.15);color:var(--cream);border:1px solid rgba(248,245,240,.25)"><i class="fa-solid {{ $i->icon }}"></i> {{ $i->name }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>

<section class="section" style="padding-top:48px">
    <div class="container">
        <div class="row" style="justify-content:space-between;margin-bottom:28px">
            <a href="{{ route('events.index') }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to events</a>
            @if(auth()->id() === $event->creator_id)
                <div class="row" style="gap:10px">
                    <a href="{{ route('events.edit', $event) }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                    <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-sm" style="color:var(--danger)"><i class="fa-solid fa-trash"></i> Delete</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="show-grid">
            {{-- ============ LEFT COLUMN ============ --}}
            <div>
                <div class="eyebrow">About this event</div>
                <p style="font-size:17px;line-height:1.7;color:var(--ink-2);margin-top:8px;white-space:pre-line">{{ $event->description }}</p>

                @if($event->destination)
                    <div style="margin-top:40px">
                        <div class="eyebrow">Held at</div>
                        <a href="{{ route('destinations.show', $event->destination) }}" class="card" style="margin-top:12px;display:flex;gap:20px;padding:20px">
                            <div class="card-media" style="width:120px;height:120px;flex-shrink:0;border-radius:var(--r-md)">
                                @if($event->destination->image_url)
                                    <img src="{{ $event->destination->image_url }}" alt="{{ $event->destination->name }}">
                                @else
                                    <div class="placeholder-media"><i class="fa-solid fa-map-location-dot"></i></div>
                                @endif
                            </div>
                            <div style="flex:1">
                                <h3 class="card-title">{{ $event->destination->name }}</h3>
                                <div class="card-sub"><i class="fa-solid fa-location-dot"></i> {{ $event->destination->city }}</div>
                            </div>
                        </a>
                    </div>
                @endif

                @if($event->latitude && $event->longitude)
                    <div style="margin-top:40px">
                        <div class="eyebrow">Location</div>
                        <div id="event-map" style="height:320px;border-radius:var(--r-lg);margin-top:12px;border:1px solid var(--line);z-index:0"></div>
                    </div>
                @endif

                {{-- ============ REVIEWS ============ --}}
                <div id="reviews" style="margin-top:48px">
                    <div class="spread" style="margin-bottom:24px">
                        <div>
                            <div class="eyebrow">Reviews</div>
                            <h2 class="h-display h3">What attendees say</h2>
                        </div>
                        @if($event->review_count > 0)
                            <div class="row" style="gap:10px">
                                <span style="font-family:var(--fd);font-size:32px;font-weight:600;color:var(--espresso)">{{ number_format((float) $event->average_rating, 1) }}</span>
                                <div>
                                    <div style="color:var(--gold);font-size:14px">{{ str_repeat('★', (int) round($event->average_rating)) }}{{ str_repeat('☆', 5 - (int) round($event->average_rating)) }}</div>
                                    <div class="muted" style="font-size:13px">{{ $event->review_count }} reviews</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @auth
                        @if(! $hasReviewed && $event->isPast())
                            <form method="POST" action="{{ route('reviews.store') }}" style="background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;margin-bottom:24px">
                                @csrf
                                <input type="hidden" name="reviewable_type" value="event">
                                <input type="hidden" name="reviewable_id" value="{{ $event->id }}">
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
                                    <textarea name="body" rows="3" required minlength="5" class="textarea" placeholder="How was the event?">{{ old('body') }}</textarea>
                                </div>
                                <button class="btn btn-primary" style="margin-top:16px"><i class="fa-solid fa-paper-plane"></i> Submit review</button>
                            </form>
                        @elseif(! $hasReviewed)
                            <div style="background:var(--cream);border-radius:var(--r-md);padding:18px;margin-bottom:24px;font-size:14px">
                                Reviews open after the event ends.
                            </div>
                        @endif
                    @else
                        <div style="background:var(--cream);border-radius:var(--r-md);padding:18px;margin-bottom:24px;font-size:14px">
                            <a href="{{ route('login') }}" style="color:var(--accent);font-weight:700">Log in</a> to write a review.
                        </div>
                    @endauth

                    <div style="display:flex;flex-direction:column;gap:20px">
                        @forelse($event->reviews as $rev)
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
                    <div class="price" style="font-size:28px">{{ $event->price > 0 ? 'R' . number_format($event->price, 0) : 'Free' }}</div>
                    <div class="row" style="margin-top:8px;font-size:13px;color:var(--muted)">
                        <i class="fa-solid fa-user-group"></i>
                        <span>{{ $event->booking_count }} attending @if($event->capacity)· {{ $event->capacity }} spots@endif</span>
                    </div>

                    @if($event->isPast())
                        <div style="background:var(--danger-soft);border:1px solid var(--danger);border-radius:var(--r-md);padding:14px;margin-top:20px;font-size:14px;color:var(--danger);font-weight:700">
                            <i class="fa-solid fa-clock-rotate-left"></i> This event has ended
                        </div>
                    @elseif($event->isFull())
                        <div style="background:var(--danger-soft);border:1px solid var(--danger);border-radius:var(--r-md);padding:14px;margin-top:20px;font-size:14px;color:var(--danger);font-weight:700">
                            <i class="fa-solid fa-user-xmark"></i> Fully booked
                        </div>
                    @elseif($myBooking)
                        <div style="background:var(--green-soft);border:1px solid var(--green);border-radius:var(--r-md);padding:14px;margin-top:20px;font-size:14px;color:var(--green);font-weight:700">
                            <i class="fa-solid fa-circle-check"></i> You're attending!
                        </div>
                        <a href="{{ route('bookings.index') }}" class="btn btn-ghost btn-block" style="margin-top:12px">View my bookings</a>
                    @elseif(auth()->check())
                        <form method="POST" action="{{ route('events.book', $event) }}" style="margin-top:20px;display:flex;flex-direction:column;gap:14px">
                            @csrf
                            <div class="field">
                                <label>Guests</label>
                                <input type="number" name="guests" value="1" min="1" max="20" class="input">
                            </div>
                            <div class="field">
                                <label>Notes (optional)</label>
                                <textarea name="notes" rows="2" class="textarea" placeholder="Any special requests?"></textarea>
                            </div>
                            <button class="btn btn-accent btn-block"><i class="fa-solid fa-calendar-check"></i> Book attendance</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-accent btn-block" style="margin-top:20px">Log in to book</a>
                    @endif

                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--line)">
                        <div class="eyebrow">Hosted by</div>
                        <div class="row" style="margin-top:10px">
                            <div class="avatar" style="width:44px;height:44px">{{ strtoupper(substr($event->creator->name, 0, 1)) }}</div>
                            <div>
                                <div style="font-weight:700;font-size:15px">{{ $event->creator->name }}</div>
                                <div class="muted" style="font-size:13px">{{ '@' . $event->creator->username }} · Event Creator</div>
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
            /* ---- Event map ---- */
            @if($event->latitude && $event->longitude)
            if (typeof L !== 'undefined' && document.getElementById('event-map')) {
                var m = L.map('event-map').setView([{{ $event->latitude }}, {{ $event->longitude }}], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(m);
                L.marker([{{ $event->latitude }}, {{ $event->longitude }}]).addTo(m).bindPopup('<b>{{ $event->title }}</b><br>{{ $event->venue }}').openPopup();
            }
            @endif
        })();
        </script>
        @endpush
        @endsection