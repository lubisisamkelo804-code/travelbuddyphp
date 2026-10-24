<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Event;
use App\Models\Review;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'reviewable_type' => 'required|in:destination,event',
            'reviewable_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'required|string|min:5|max:1000',
        ]);

        $target = $data['reviewable_type'] === 'destination'
            ? Destination::findOrFail($data['reviewable_id'])
            : Event::findOrFail($data['reviewable_id']);

        if (Review::where('author_id', Auth::id())->where($data['reviewable_type'] . '_id', $target->id)->exists()) {
            return back()->with('error', 'You have already reviewed this.');
        }

        Review::create([
            'author_id' => Auth::id(),
            'destination_id' => $data['reviewable_type'] === 'destination' ? $target->id : null,
            'event_id' => $data['reviewable_type'] === 'event' ? $target->id : null,
            'rating' => $data['rating'], 'body' => $data['body'],
        ]);

        $this->recalculate($target, $data['reviewable_type']);

        $user = Auth::user();
        $user->increment('review_points', 10);
        $p = $user->review_points;
        if ($p >= 1000) $user->update(['rank' => 'Legend']);
        elseif ($p >= 500) $user->update(['rank' => 'Veteran']);
        elseif ($p >= 100) $user->update(['rank' => 'Explorer']);

        $owner = $data['reviewable_type'] === 'destination' ? $target->owner : $target->creator;
        $label = $data['reviewable_type'] === 'destination' ? $target->name : $target->title;
        Notifier::send($owner, $user->name . ' rated ' . $label . ' ' . $data['rating'] . '★',
            route($data['reviewable_type'] . 's.show', $target));

        return back()->with('success', 'Review submitted — thank you!');
    }

    public function destroy(Review $review)
    {
        abort_unless($review->author_id === Auth::id(), 403);
        $type = $review->destination_id ? 'destination' : 'event';
        $target = $review->destination ?? $review->event;
        $review->delete();
        if ($target) $this->recalculate($target, $type);
        return back()->with('success', 'Review deleted.');
    }

    private function recalculate($target, string $type): void
    {
        $avg = Review::where($type . '_id', $target->id)->avg('rating');
        $count = Review::where($type . '_id', $target->id)->count();
        $target->update(['average_rating' => round($avg ?? 0, 2), 'review_count' => $count]);
    }
}