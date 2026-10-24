<?php

namespace App\Http\Controllers;

use App\Support\FeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $r, FeedService $feed)
    {
        $cat = (string) $r->query('cat', 'all');

        return view('home', [
            'feed' => $feed->mixed(Auth::user(), $cat, 30),
            'cat' => $cat,
        ]);
    }
}