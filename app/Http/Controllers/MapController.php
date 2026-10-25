<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Event;

class MapController extends Controller
{
    public function index()
    {
        $destinations = Destination::whereNotNull('latitude')
            ->get(['id', 'owner_id', 'name', 'city', 'latitude', 'longitude', 'average_rating', 'review_count']);

        $events = Event::whereNotNull('latitude')->where('end_date', '>=', now())
            ->get(['id', 'creator_id', 'title', 'city', 'latitude', 'longitude', 'average_rating', 'review_count']);

        return view('map', compact('destinations', 'events'));
    }
}