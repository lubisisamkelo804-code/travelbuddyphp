<?php

return [
    'rail' => [
        'enabled' => env('RAIL_ENABLED', true),
        'widgets' => [
            'weather' => true, 'trending' => true, 'live' => true, 'events' => true,
            'tip' => true, 'currency' => true, 'photo' => true, 'people' => true,
            'quote' => true, 'minimap' => true,
        ],
        'weather' => ['city' => 'Johannesburg', 'lat' => -26.2041, 'lng' => 28.0473],
        'currency' => ['base' => 'ZAR', 'list' => ['ZAR', 'USD', 'EUR', 'GBP', 'JPY', 'AED', 'KES', 'BWP']],
    ],
];