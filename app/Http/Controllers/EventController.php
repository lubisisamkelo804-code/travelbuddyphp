<?php

namespace App\Http\Controllers;

use App\Events\PlacePublished;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Event;
use App\Models\Interest;
use App\Models\Post;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $r)
    {
        $query = Event::with('interests');

        if ($search = $r->query('q')) {
            $query->where(fn ($w) => $w->where('title', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('venue', 'like', "%$search%"));
        }
        if ($city = $r->query('city')) $query->where('city', 'like', "%$city%");
        if ($max = $r->query('max_price')) $query->where('price', '<=', $max);
        if ($from = $r->query('from')) $query->whereDate('start_date', '>=', $from);

        $scope = $r->query('scope', 'upcoming');
        if ($scope === 'upcoming') $query->where('end_date', '>=', now())->orderBy('start_date');
        elseif ($scope === 'past') $query->where('end_date', '<', now())->orderByDesc('start_date');
        else $query->latest();

        return view('events.index', [
            'events' => $query->paginate(12)->withQueryString(),
            'scope' => $scope,
        ]);
    }

    public function show(Event $event)
    {
        $event->load(['creator', 'destination', 'interests', 'reviews.author']);
        $user = Auth::user();

        return view('events.show', [
            'event' => $event,
            'hasReviewed' => $user ? $event->reviews->contains('author_id', $user->id) : false,
            'myBooking' => $user ? Booking::where('user_id', $user->id)->where('event_id', $event->id)
                ->where('status', '!=', 'cancelled')->first() : null,
        ]);
    }

    public function create()
    {
        return view('events.form', [
            'event' => null,
            'interests' => Interest::orderBy('name')->get(),
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validateEvent($r, isNew: true);
        $event = Event::create(['creator_id' => Auth::id()] + $data);
        if ($r->has('interests')) $event->interests()->sync($r->interests);

        if ($event->latitude && $event->longitude) {
            broadcast(new PlacePublished([
                'kind' => 'event', 'id' => $event->id, 'name' => $event->title,
                'city' => $event->city, 'lat' => (float) $event->latitude,
                'lng' => (float) $event->longitude,
                'url' => route('events.show', $event) . '#book',
            ]))->toOthers();
        }

        return redirect()->route('events.show', $event)->with('success', 'Event created!');
    }

    public function edit(Event $event)
    {
        abort_unless($event->creator_id === Auth::id(), 403);
        return view('events.form', [
            'event' => $event,
            'interests' => Interest::orderBy('name')->get(),
            'destinations' => Destination::orderBy('name')->get(),
        ]);
    }

    public function update(Request $r, Event $event)
    {
        abort_unless($event->creator_id === Auth::id(), 403);
        $event->update($this->validateEvent($r));
        if ($r->has('interests')) $event->interests()->sync($r->interests);
        return redirect()->route('events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        abort_unless($event->creator_id === Auth::id(), 403);
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted.');
    }

    public function book(Request $r, Event $event)
    {
        $data = $r->validate([
            'guests' => 'integer|min:1|max:20', 'notes' => 'nullable|string|max:500',
        ]);
        $guests = max(1, (int) ($data['guests'] ?? 1));

        if ($event->external_booking_url) return redirect()->away($event->external_booking_url);
        if ($event->isPast()) return back()->with('error', 'This event has already ended.');
        if ($event->bookingClosed()) return back()->with('error', 'Bookings for this event have closed.');

        // one active booking per user per event
        if (Booking::where('user_id', Auth::id())->where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'You have already booked this event.');
        }

        // lock the row to avoid race conditions on capacity checks
        $fresh = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
        if ($fresh->isFull()) return back()->with('error', 'This event is fully booked.');
        if ($fresh->capacity !== null && $fresh->booking_count + $guests > $fresh->capacity) {
            return back()->with('error', 'Only ' . max(0, $fresh->capacity - $fresh->booking_count) . ' spot(s) left — try fewer guests.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($fresh, $guests, $data) {
            Booking::create([
                'user_id' => Auth::id(), 'event_id' => $fresh->id,
                'guests' => $guests, 'notes' => $data['notes'] ?? null,
            ]);

            $fresh->increment('booking_count');
            $fresh->refresh();

            if ($fresh->isFull()) {
                Post::where('event_id', $fresh->id)->where('type', Post::TYPE_EVENT)->delete();
            }

            Notifier::send($fresh->creator, Auth::user()->name . ' is attending ' . $fresh->title, route('bookings.index'));
        });

        return back()->with('success', "You're attending!");
    }

    private function validateEvent(Request $r, bool $isNew = false): array
    {
        $data = $r->validate([
            'title' => 'required|string|min:2|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'venue' => 'required|string|max:255',
            'city' => 'required|string|max:150',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'start_date' => 'required|date' . ($isNew ? '|after:now' : ''),
            'end_date' => 'required|date|after:start_date',
            'booking_end_date' => 'nullable|date|before_or_equal:start_date',
            'external_booking_url' => 'nullable|url|max:500',
            'capacity' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'destination_id' => 'nullable|exists:destinations,id',
        ]);

        if ($r->hasFile('image')) {
            $data['image_url'] = asset('storage/' . $r->file('image')->store('events', 'public'));
        }
        unset($data['image']);

        $data['price'] = $data['price'] ?? 0;
        return $data;
    }
}