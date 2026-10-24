<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    private const FALLBACK = [
        'USD' => 1, 'EUR' => 0.92, 'GBP' => 0.79, 'ZAR' => 18.40,
        'JPY' => 157, 'AED' => 3.67, 'MAD' => 9.90, 'BWP' => 13.60,
        'KES' => 129, 'NZD' => 1.64,
    ];

    public function rates(): array
    {
        return Cache::remember('rail.fx.usd', 86400, function () {
            try {
                $json = Http::timeout(4)->get('https://open.er-api.com/v6/latest/USD')->throw()->json();
                return $json['rates'] ?? self::FALLBACK;
            } catch (\Throwable $e) {
                return self::FALLBACK;
            }
        });
    }

    public function convert(float $amount, string $from, string $to): ?array
    {
        $rates = $this->rates();
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));

        if ($amount <= 0 || ! isset($rates[$from]) || ! isset($rates[$to])) return null;

        $rate = $rates[$to] / $rates[$from];

        return [
            'amount' => round($amount, 2),
            'from' => $from,
            'to' => $to,
            'result' => round($amount * $rate, 2),
            'rate' => round($rate, 4),
            'live' => count($rates) > 20,
        ];
    }
}