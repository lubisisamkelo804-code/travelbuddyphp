<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Event;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $r)
    {
        $q = trim((string) $r->query('q', ''));
        $cat = (string) $r->query('category', 'all');
        if (! in_array($cat, ['all', 'destinations', 'events', 'posts', 'people'], true)) $cat = 'all';

        $results = ['destinations' => collect(), 'events' => collect(), 'posts' => collect(), 'people' => collect()];

        if ($q !== '') {
            $like = "%{$q}%";

            if (in_array($cat, ['all', 'destinations'])) {
                $results['destinations'] = Destination::with('interests')
                    ->where(fn ($w) => $w->where('name', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('description', 'like', $like))
                    ->limit(20)->get();
            }
            if (in_array($cat, ['all', 'events'])) {
                $results['events'] = Event::where(fn ($w) => $w->where('title', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('venue', 'like', $like))
                    ->limit(20)->get();
            }
            if (in_array($cat, ['all', 'posts'])) {
                $results['posts'] = Post::with(['author', 'media'])->where('content', 'like', $like)->limit(20)->get();
            }
            if (in_array($cat, ['all', 'people'])) {
                $results['people'] = User::where(fn ($w) => $w->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like))
                    ->limit(20)->get();
            }
        }

        $none = $q !== ''
            && $results['destinations']->isEmpty()
            && $results['events']->isEmpty()
            && $results['posts']->isEmpty()
            && $results['people']->isEmpty();

        return view('search', compact('q', 'cat', 'results', 'none'));
    }
}