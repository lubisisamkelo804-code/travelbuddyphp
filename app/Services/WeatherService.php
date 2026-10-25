<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherService
{
    public function current(?string $city = null, ?float $lat = null, ?float $lng = null): array
    {
        $cfg = config('travelbuddy.rail.weather', []);
        $city = $city ?: ($cfg['city'] ?? 'Johannesburg');
        $lat = $lat ?? ($cfg['lat'] ?? -26.2041);
        $lng = $lng ?? ($cfg['lng'] ?? 28.0473);

        return Cache::remember("rail.wx.{$lat}.{$lng}", 1800, function () use ($city, $lat, $lng) {
            try {
                $res = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude' => $lat, 'longitude' => $lng,
                    'current' => 'temperature_2m,apparent_temperature,wind_speed_10m,weather_code',
                    'daily' => 'temperature_2m_max,temperature_2m_min',
                    'timezone' => 'auto',
                ])->throw()->json();

                $code = (int) ($res['current']['weather_code'] ?? 0);

                return [
                    'city' => $city,
                    'temp' => (int) round($res['current']['temperature_2m'] ?? 0),
                    'feels' => (int) round($res['current']['apparent_temperature'] ?? 0),
                    'wind' => (int) round($res['current']['wind_speed_10m'] ?? 0),
                    'hi' => (int) round($res['daily']['temperature_2m_max'][0] ?? 0),
                    'lo' => (int) round($res['daily']['temperature_2m_min'][0] ?? 0),
                    'label' => $this->label($code),
                    'icon' => $this->icon($code),
                    'updated' => now()->format('H:i'),
                ];
            } catch (\Throwable $e) {
                return [
                    'city' => $city, 'temp' => 22, 'feels' => 23, 'wind' => 14,
                    'hi' => 25, 'lo' => 16, 'label' => 'Clear skies', 'icon' => '☀️',
                    'updated' => now()->format('H:i'), 'offline' => true,
                ];
            }
        });
    }

    private function label(int $c): string
    {
        if ($c === 0) return 'Clear skies';
        if ($c <= 3) return 'Partly cloudy';
        if ($c <= 48) return 'Foggy';
        if ($c <= 67) return 'Rain showers';
        if ($c <= 77) return 'Snow';
        if ($c <= 82) return 'Showers';
        if ($c <= 99) return 'Thunder';
        return 'Unsettled';
    }

    private function icon(int $c): string
    {
        if ($c === 0) return '☀️';
        if ($c <= 3) return '⛅';
        if ($c <= 48) return '🌫️';
        if ($c <= 67) return '🌦️';
        if ($c <= 77) return '❄️';
        if ($c <= 82) return '🌧️';
        return '⛈️';
    }
}