<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Event;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function ask(Request $r)
    {
        $m = mb_strtolower(trim((string) $r->input('message', '')));
        if ($m === '') return response()->json(['reply' => 'Ask me anything about TravelBuddy!']);

        $reply = match (true) {
            (bool) preg_match('/\b(hi|hello|hey|howdy)\b/', $m) =>
                'Hello, traveler! 👋 I can help with <b>destinations</b>, <b>events</b>, <b>booking</b>, <b>reviews</b>, the <b>map</b> and your <b>inbox</b>.',
            str_contains($m, 'book') =>
                'Open any destination or event page and use the booking panel on the right. Manage everything under <b>Bookings</b>. <a href="' . route('destinations.index') . '">Browse destinations →</a>',
            str_contains($m, 'event') => $this->events(),
            str_contains($m, 'destination') || str_contains($m, 'place') || str_contains($m, 'visit') || str_contains($m, 'go') => $this->destinations(),
            str_contains($m, 'map') =>
                'The interactive map shows every pinned destination and event. <a href="' . route('map') . '">Open the map →</a>',
            str_contains($m, 'inbox') || str_contains($m, 'message') || str_contains($m, 'chat') =>
                'Your <b>Inbox</b> lets you message any traveler and share posts with them. <a href="' . route('inbox.index') . '">Open inbox →</a>',
            str_contains($m, 'review') || str_contains($m, 'rate') || str_contains($m, 'star') =>
                'On any destination or past event page, scroll to <b>Reviews</b>, tap the stars (1–5), write your experience and submit. You earn <b>+10 points</b> per review!',
            str_contains($m, 'price') || str_contains($m, 'cost') || str_contains($m, 'budget') =>
                'Use the <b>Max price</b> filter on the Destinations page, or set your budget level in your profile to personalise recommendations.',
            str_contains($m, 'logout') || str_contains($m, 'log out') =>
                'Use the <b>Log out</b> button at the bottom of the left sidebar.',
            str_contains($m, 'profile') || str_contains($m, 'avatar') || str_contains($m, 'picture') =>
                'On your <b>Profile</b> page you can upload a profile picture from your device, edit your bio, add social links and pick interests. <a href="' . route('profile') . '">Open profile →</a>',
            str_contains($m, 'post') =>
                'Tap <b>Create Post</b> in the sidebar. You can upload up to 4 photos or videos straight from your device.',
            str_contains($m, 'help') =>
                'I can: 🗺️ suggest top destinations · 📅 list upcoming events · explain booking & reviews · point you to your inbox & map · 🔍 search anything.',
            default => $this->fallback($m),
        };

        return response()->json(['reply' => $reply]);
    }

    private function destinations(): string
    {
        $top = Destination::orderByDesc('average_rating')->limit(3)->get();
        if ($top->isEmpty()) return 'No destinations yet — check back soon!';

        $lines = $top->map(fn ($d) => '• <a href="' . route('destinations.show', $d) . '"><b>' . e($d->name) . '</b></a> — ' . e($d->city) . ' ★ ' . number_format((float) $d->average_rating, 1))->implode('<br>');
        return 'Top-rated places right now:<br>' . $lines;
    }

    private function events(): string
    {
        $next = Event::where('end_date', '>=', now())->orderBy('start_date')->limit(3)->get();
        if ($next->isEmpty()) return 'No upcoming events at the moment.';

        $lines = $next->map(fn ($e) => '• <a href="' . route('events.show', $e) . '"><b>' . e($e->title) . '</b></a> — ' . $e->start_date->format('d M Y, H:i'))->implode('<br>');
        return 'Upcoming events:<br>' . $lines;
    }

    private function fallback(string $m): string
    {
        $hit = Destination::where('name', 'like', "%{$m}%")->orWhere('city', 'like', "%{$m}%")->first();
        if ($hit) {
            return 'I found <a href="' . route('destinations.show', $hit) . '"><b>' . e($hit->name) . '</b></a> in ' . e($hit->city) . ' ★ ' . number_format((float) $hit->average_rating, 1) . '. Want to book it?';
        }
        return 'Hmm, I don\'t have that yet. Try asking about <b>destinations</b>, <b>events</b>, <b>booking</b>, <b>reviews</b>, the <b>map</b> or your <b>inbox</b> — or search: <a href="' . route('search', ['q' => $m]) . '">search "' . e($m) . '" →</a>';
    }
}