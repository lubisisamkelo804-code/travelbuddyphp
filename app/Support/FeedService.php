<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Destination;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FeedService
{
    public function mixed(?User $me, string $cat = 'all', int $limit = 30): Collection
    {
        [$visited, $bookedEvents, $liked] = $this->signals($me);
        $visitedInterests = $this->interestIdsOf('destination', $visited);
        $eventInterests = $this->interestIdsOf('event', $bookedEvents);

        $items = collect();

        Post::with(['author', 'media', 'destination', 'event'])->latest()->limit(60)->get()
            ->each(fn (Post $p) => $items->push([
                'kind' => 'post', 'item' => $p, 'created' => $p->created_at,
                'score' => $this->postScore($p, $me, $visited, $liked),
            ]));

        Destination::with('interests')->latest()->limit(60)->get()
            ->each(fn (Destination $d) => $items->push([
                'kind' => 'destination', 'item' => $d, 'created' => $d->created_at,
                'score' => $this->destinationScore($d, $visited, $visitedInterests),
            ]));

        Event::with('interests')->where('end_date', '>=', now())->latest()->limit(60)->get()
            ->each(fn (Event $e) => $items->push([
                'kind' => 'event', 'item' => $e, 'created' => $e->created_at,
                'score' => $this->eventScore($e, $bookedEvents, $eventInterests),
            ]));

        if ($cat !== 'all') {
            $items = $items->filter(fn ($x) => $this->matchesCategory($x, $cat));
        }

        return $items
            ->map(fn ($x) => $x + ['sortKey' => (mt_rand(1, 10000) / 10000) * 0.45 + (min($x['score'], 6) / 6) * 0.55])
            ->sortByDesc('sortKey')
            ->take($limit)
            ->values();
    }

    public function recommendedDestinations(?User $me, int $limit = 12): Collection
    {
        [$visited, , ] = $this->signals($me);
        $interests = $this->interestIdsOf('destination', $visited);

        return Destination::with('interests')->get()
            ->map(fn (Destination $d) => $d->setAttribute('_score', $this->destinationScore($d, $visited, $interests)))
            ->sortByDesc('_score')->take($limit)->values();
    }

    public function recommendedEvents(?User $me, int $limit = 12): Collection
    {
        [, $bookedEvents, ] = $this->signals($me);
        $interests = $this->interestIdsOf('event', $bookedEvents);

        return Event::with('interests')->where('end_date', '>=', now())->get()
            ->map(fn (Event $e) => $e->setAttribute('_score', $this->eventScore($e, $bookedEvents, $interests)))
            ->sortByDesc('_score')->take($limit)->values();
    }

    private function signals(?User $me): array
    {
        if (! $me) return [collect(), collect(), collect()];

        $visited = Booking::where('user_id', $me->id)->whereNotNull('destination_id')->pluck('destination_id')->unique();
        $bookedEvents = Booking::where('user_id', $me->id)->whereNotNull('event_id')->pluck('event_id')->unique();

        return [$visited, $bookedEvents, $this->likedDestinationIds($me)];
    }

    private function likedDestinationIds(User $me): Collection
    {
        $commentIds = CommentReaction::where('user_id', $me->id)->where('type', 'like')->pluck('comment_id');
        $postIds = Comment::whereIn('id', $commentIds)->pluck('post_id');

        return Post::whereIn('id', $postIds)->whereNotNull('destination_id')->pluck('destination_id')->unique();
    }

    private function interestIdsOf(string $kind, Collection $ids): Collection
    {
        if ($ids->isEmpty()) return collect();
        $table = $kind === 'destination' ? 'destination_interests' : 'event_interests';
        $column = $kind === 'destination' ? 'destination_id' : 'event_id';

        return DB::table($table)->whereIn($column, $ids)->pluck('interest_id')->unique();
    }

    private function postScore(Post $p, ?User $me, Collection $visited, Collection $liked): float
    {
        $s = 1.0;
        if ($p->destination_id && $visited->contains($p->destination_id)) $s += 2;
        if ($p->destination_id && $liked->contains($p->destination_id)) $s += 1;
        if ($me && $p->author_id === $me->id) $s += 0.5;
        if ($p->created_at->gt(now()->subDays(3))) $s += 0.5;
        return $s;
    }

    private function destinationScore(Destination $d, Collection $visited, Collection $vi): float
    {
        $s = 1.0 + ((float) $d->average_rating) / 5;
        if ($visited->contains($d->id)) $s += 3;
        if ($vi->intersect($d->interests->pluck('id'))->isNotEmpty()) $s += 2;
        return $s;
    }

    private function eventScore(Event $e, Collection $booked, Collection $ei): float
    {
        $s = 1.0;
        if ($booked->contains($e->id)) $s += 3;
        if ($ei->intersect($e->interests->pluck('id'))->isNotEmpty()) $s += 2;
        if ($e->start_date->lte(now()->addDays(7))) $s += 1;
        return $s;
    }

    private function matchesCategory(array $x, string $cat): bool
    {
        return match ($cat) {
            'posts' => $x['kind'] === 'post',
            'destinations' => $x['kind'] === 'destination',
            'events' => $x['kind'] === 'event',
            default => $this->hasInterest($x, $cat),
        };
    }

    private function hasInterest(array $x, string $name): bool
    {
        $item = $x['item'];
        $names = match ($x['kind']) {
            'destination', 'event' => $item->interests->pluck('name'),
            'post' => $item->destination?->interests->pluck('name') ?? collect(),
        };
        return $names->map(fn ($n) => strtolower($n))->contains(strtolower($name));
    }
}