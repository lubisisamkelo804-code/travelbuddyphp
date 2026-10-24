<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\FeedService;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function explore(FeedService $feed)
    {
        $me = Auth::user();

        return view('explore', [
            'destinations' => $feed->recommendedDestinations($me, 12),
            'events' => $feed->recommendedEvents($me, 12),
            'businessPosts' => Post::with(['author', 'media', 'destination'])
                ->where('type', Post::TYPE_BUSINESS)->latest()->limit(10)->get(),
            'personalized' => $me !== null,
        ]);
    }
}