<?php

namespace App\Http\Controllers;

use App\Events\PlacePublished;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Interest;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinationController extends Controller
{
    public function index(Request $r)
    {
        $query = Destination::with('interests');

        if ($search = $r->query('q')) {
            $query->where(fn ($w) => $w->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('city', 'like', "%$search%"));
        }
        if ($city = $r->query('city')) $query->where('city', 'like', "%$city%");
        if ($max = $r->query('max_cost')) $query->where('estimated_cost', '<=', $max);
        if ($ints = $r->query('interests')) {
            $query->whereHas('interests', fn ($w) => $w->whereIn('interests.id', (array) $ints));
        }

        switch ($r->query('sort', 'newest')) {
            case 'rating':    $query->orderByDesc('average_rating'); break;
            case 'cost_low':  $query->orderBy('estimated_cost'); break;
            case 'cost_high': $query->orderByDesc('estimated_cost'); break;
            case 'popular':   $query->orderByDesc('booking_count'); break;
            default:          $query->latest();
        }

        return view('destinations.index', [
            'destinations' => $query->paginate(12)->withQueryString(),
            'interests' => Interest::orderBy('name')->get(),
        ]);
    }

    public function show(Destination $destination)
    {
        $destination->increment('view_count');
        $destination->load(['owner', 'interests', 'reviews.author']);

        $user = Auth::user();

        return view('destinations.show', [
            'destination' => $destination,
            'hasReviewed' => $user ? $destination->reviews->contains('author_id', $user->id) : false,
            'myBooking' => $user ? Booking::where('user_id', $user->id)
                ->where('destination_id', $destination->id)
                ->where('status', '!=', 'cancelled')->first() : null,
        ]);
    }

    public function create()
    {
        return view('destinations.form', ['destination' => null, 'interests' => Interest::orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $data = $this->validateDestination($r);
        $destination = Destination::create(['owner_id' => Auth::id()] + $data);
        if ($r->has('interests')) $destination->interests()->sync($r->interests);

        if ($destination->latitude && $destination->longitude) {
            broadcast(new PlacePublished([
                'kind' => 'destination', 'id' => $destination->id, 'name' => $destination->name,
                'city' => $destination->city, 'lat' => (float) $destination->latitude,
                'lng' => (float) $destination->longitude,
                'url' => route('owners.show', $destination->owner_id),
            ]))->toOthers();
        }

        return redirect()->route('destinations.show', $destination)->with('success', 'Destination published!');
    }

    public function edit(Destination $destination)
    {
        abort_unless($destination->owner_id === Auth::id(), 403);
        return view('destinations.form', ['destination' => $destination, 'interests' => Interest::orderBy('name')->get()]);
    }

    public function update(Request $r, Destination $destination)
    {
        abort_unless($destination->owner_id === Auth::id(), 403);
        $destination->update($this->validateDestination($r));
        if ($r->has('interests')) $destination->interests()->sync($r->interests);
        return redirect()->route('destinations.show', $destination)->with('success', 'Destination updated.');
    }

    public function destroy(Destination $destination)
    {
        abort_unless($destination->owner_id === Auth::id(), 403);
        $destination->delete();
        return redirect()->route('destinations.index')->with('success', 'Destination deleted.');
    }

    public function book(Request $r, Destination $destination)
    {
        $r->validate(['guests' => 'integer|min:1|max:20', 'booked_date' => 'nullable|date', 'notes' => 'nullable|max:500']);

        if ($destination->capacity && $destination->booking_count >= $destination->capacity) {
            return back()->with('error', 'Sorry, this destination is fully booked.');
        }
        if (Booking::where('user_id', Auth::id())->where('destination_id', $destination->id)
            ->where('status', '!=', 'cancelled')->exists()) {
            return back()->with('error', 'You already have an active booking here.');
        }

        Booking::create([
            'user_id' => Auth::id(), 'destination_id' => $destination->id,
            'guests' => $r->integer('guests') ?: 1, 'notes' => $r->input('notes'),
            'booked_date' => $r->input('booked_date'),
        ]);

        $destination->increment('booking_count');
        Notifier::send($destination->owner, Auth::user()->name . ' booked ' . $destination->name, route('bookings.index'));

        return back()->with('success', 'Booking confirmed!');
    }

    private function validateDestination(Request $r): array
    {
        $data = $r->validate([
            'name' => 'required|string|min:2|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'city' => 'required|string|max:150',
            'country' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'estimated_cost' => 'required|numeric|min:0',
            'capacity' => 'nullable|integer|min:1',
        ]);

        if ($r->hasFile('image')) {
            $file = $r->file('image');
            $ext  = strtolower($file->extension() ?: 'jpg');
            $path = $file->storeAs('destinations', Str::random(20) . '.' . $ext, 'public');
            // Store the relative path; the model/view resolves to a URL with a
            // fallback for missing storage symlinks (XAMPP/Windows).
            $data['image_url'] = $path;
        }
        unset($data['image']);

        return $data;
    }
}