<?php

namespace App\Support;

use App\Services\CurrencyService;
use App\Services\WeatherService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TravelRail
{
    private static array $colCache = [];

    private array $tips = [
        'Pack one outfit you would happily be photographed in — you will be.',
        'Book the 6am departure. Empty markets, soft light, no queues.',
        'Carry a paper map for the day you lose signal. It becomes the story.',
        'Learn five words of the local language before you land. Doors open.',
        'Keep 20% of your budget unspent. The best days are unplanned.',
        'Ask where locals eat, never "where is good food".',
        'Photograph the ticket, the menu, the receipt. Memory lives in paper.',
        'Sit at the bar. Strangers are the itinerary you cannot book.',
    ];

    private array $quotes = [
        ['We travel not to escape life, but for life not to escape us.', 'Anonymous'],
        ['A place is never just a place once someone has laughed in it.', 'TravelBuddy'],
        ['The map is not the territory — but it is a lovely excuse.', 'After Korzybski'],
        ['Home is wherever you stop rushing.', 'Unknown'],
    ];

    public function __construct(private WeatherService $wx, private CurrencyService $fx)
    {
    }

    public function build(): array
    {
        $cfg = config('travelbuddy.rail', []);
        if (empty($cfg['enabled'])) return [];

        $out = [];
        foreach (($cfg['widgets'] ?? []) as $key => $on) {
            if (! $on) continue;
            try {
                $value = $this->render((string) $key);
            } catch (\Throwable $e) {
                $value = null;
            }
            if (! empty($value)) $out[$key] = $value;
        }
        return $out;
    }

    private function render(string $key)
    {
        return match ($key) {
            'weather' => $this->wx->current(),
            'trending' => $this->trending(),
            'live' => $this->live(),
            'events' => $this->events(),
            'tip' => ['text' => $this->tips[now()->dayOfYear % count($this->tips)]],
            'currency' => $this->currency(),
            'photo' => $this->photo(),
            'people' => $this->people(),
            'quote' => $this->quote(),
            'minimap' => $this->minimap(),
            default => null,
        };
    }

    private function trending(): array
    {
        if (! Schema::hasTable('destinations')) return [];

        $nameCol = $this->col('destinations', ['name', 'title']);
        $cityCol = $this->col('destinations', ['city', 'town', 'location']);
        $ctryCol = $this->col('destinations', ['country', 'region']);
        $imgCol = $this->col('destinations', ['image_url', 'image', 'cover_image', 'photo']);
        $priceCol = $this->col('destinations', ['price_from', 'price', 'estimated_cost', 'nightly_rate']);
        $rankCol = $this->col('destinations', ['review_points', 'views', 'popularity', 'booking_count', 'average_rating']);

        $query = DB::table('destinations');
        if ($rankCol) $query->orderByDesc($rankCol);
        $rows = $query->orderByDesc('id')->limit(4)->get();

        $items = [];
        foreach ($rows as $row) {
            $city = $this->val($row, [$cityCol]);
            $country = $this->val($row, [$ctryCol]);
            $place = trim(implode(', ', array_filter([$city, $country])));

            $items[] = [
                'id' => $row->id,
                'name' => e($this->val($row, [$nameCol], 'Untitled')),
                'place' => e($place),
                'image' => $this->val($row, [$imgCol], ''),
                'price' => $priceCol ? (float) $this->val($row, [$priceCol], 0) : null,
            ];
        }
        return $items;
    }

    private function live(): array
    {
        $feed = [];
        $feed = array_merge($feed, $this->liveFrom('destinations', '📍', 'published a new place'));
        $feed = array_merge($feed, $this->liveFrom('bookings', '🧳', 'booked a trip'));
        $feed = array_merge($feed, $this->liveFrom('reviews', '✍️', 'left a review'));
        $feed = array_merge($feed, $this->liveFrom('posts', '📸', 'posted to the feed'));

        usort($feed, fn ($a, $b) => $b['ts'] <=> $a['ts']);
        $feed = array_slice($feed, 0, 6);

        $items = [];
        foreach ($feed as $f) {
            $items[] = ['icon' => $f['icon'], 'html' => $f['html'], 'when' => $f['when']];
        }
        return $items;
    }

    private function liveFrom(string $table, string $icon, string $verb): array
    {
        if (! Schema::hasTable($table)) return [];

        $timeCol = $this->col($table, ['created_at']);
        $userCol = $this->col($table, ['user_id', 'author_id', 'owner_id', 'creator_id']);
        if (! $timeCol) return [];

        try {
            $rows = DB::table($table)->orderByDesc($timeCol)->limit(2)->get();
        } catch (\Throwable $e) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            $who = $userCol ? $this->userName($row->$userCol ?? null) : 'A traveler';
            $ts = 0;
            try {
                $ts = Carbon::parse($row->$timeCol)->timestamp;
            } catch (\Throwable $e) {
                $ts = 0;
            }
            $items[] = [
                'icon' => $icon,
                'html' => '<b>' . e($who) . '</b> ' . e($verb),
                'ts' => $ts,
                'when' => $this->timeAgo($ts),
            ];
        }
        return $items;
    }

    private function events(): array
    {
        if (! Schema::hasTable('events')) return [];

        $whenCol = $this->col('events', ['start_date', 'date', 'event_date', 'starts_at']);
        $titleCol = $this->col('events', ['title', 'name']);
        $placeCol = $this->col('events', ['city', 'location', 'venue']);

        $query = DB::table('events');
        if ($whenCol) $query->where($whenCol, '>=', now()->toDateString())->orderBy($whenCol);
        $rows = $query->limit(3)->get();

        $items = [];
        foreach ($rows as $row) {
            $when = $whenCol ? $this->val($row, [$whenCol]) : null;
            $items[] = [
                'id' => $row->id,
                'title' => e($this->val($row, [$titleCol], 'Community gathering')),
                'place' => e((string) $this->val($row, [$placeCol], '')),
                'when' => $when ? $this->stamp($when) : '',
            ];
        }
        return $items;
    }

    private function currency(): array
    {
        $cfg = config('travelbuddy.rail.currency', []);
        $base = $cfg['base'] ?? 'ZAR';
        $list = $cfg['list'] ?? ['ZAR', 'USD', 'EUR', 'GBP'];

        $result = null;
        $amt = request()->query('amt');
        $from = request()->query('from');
        $to = request()->query('to');
        if ($amt && $from && $to) {
            $result = $this->fx->convert((float) $amt, (string) $from, (string) $to);
        }

        return ['base' => $base, 'list' => $list, 'result' => $result];
    }

    private function photo(): array
    {
        if (! Schema::hasTable('posts')) return [];
        $imgCol = $this->col('posts', ['image_url', 'image', 'photo_url', 'media']);
        if (! $imgCol) return [];

        $row = DB::table('posts')->whereNotNull($imgCol)->where($imgCol, '!=', '')->orderByDesc('id')->first();
        if (! $row) return [];

        $bodyCol = $this->col('posts', ['content', 'body', 'caption', 'text']);
        $userCol = $this->col('posts', ['author_id', 'user_id']);

        return [
            'image' => $this->val($row, [$imgCol]),
            'caption' => e(Str::limit((string) $this->val($row, [$bodyCol], 'A moment from the road.'), 110)),
            'author' => e($this->userName($userCol ? ($row->$userCol ?? null) : null)),
        ];
    }

    private function people(): array
    {
        if (! Schema::hasTable('users')) return [];
        $me = auth()->id();
        $found = [];

        if ($me && Schema::hasTable('user_interests') && Schema::hasTable('interests')) {
            try {
                $mine = DB::table('user_interests')->where('user_id', $me)->pluck('interest_id')->all();
                if (! empty($mine)) {
                    $labels = DB::table('interests')->pluck('name', 'id');
                    $rows = DB::table('user_interests as ui')
                        ->join('users as u', 'u.id', '=', 'ui.user_id')
                        ->whereIn('ui.interest_id', $mine)
                        ->where('u.id', '!=', $me)
                        ->select('u.id', 'u.name', 'u.avatar_url', 'u.avatar_path', 'ui.interest_id')
                        ->limit(30)->get();

                    foreach ($rows as $r) {
                        if (! isset($found[$r->id])) {
                            $found[$r->id] = ['id' => $r->id, 'name' => e($r->name), 'avatar' => $this->avatarOf($r), 'tags' => []];
                        }
                        if (count($found[$r->id]['tags']) < 2) {
                            $label = $labels[$r->interest_id] ?? '';
                            if ($label !== '') $found[$r->id]['tags'][] = e($label);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $found = [];
            }
        }

        if (empty($found)) {
            try {
                $rows = DB::table('users')->where('id', '!=', $me ?? 0)->orderByDesc('id')->limit(3)
                    ->get(['id', 'name', 'avatar_url', 'avatar_path']);
                foreach ($rows as $r) {
                    $found[$r->id] = ['id' => $r->id, 'name' => e($r->name), 'avatar' => $this->avatarOf($r), 'tags' => []];
                }
            } catch (\Throwable $e) {
                return [];
            }
        }

        return array_slice(array_values($found), 0, 3);
    }

    private function avatarOf(object $row): ?string
    {
        if (! empty($row->avatar_path)) return asset('storage/' . $row->avatar_path);
        return ! empty($row->avatar_url) ? $row->avatar_url : null;
    }

    private function quote(): array
    {
        $pair = $this->quotes[now()->dayOfYear % count($this->quotes)];
        return ['text' => e($pair[0]), 'by' => e($pair[1])];
    }

    private function minimap(): array
    {
        if (! Schema::hasTable('destinations')) return [];
        $latCol = $this->col('destinations', ['latitude', 'lat']);
        $lngCol = $this->col('destinations', ['longitude', 'lng', 'lon']);
        $nameCol = $this->col('destinations', ['name', 'title']);
        if (! $latCol || ! $lngCol) return [];

        $rows = DB::table('destinations')->whereNotNull($latCol)->whereNotNull($lngCol)->limit(6)->get();
        $items = [];
        foreach ($rows as $row) {
            $items[] = [
                'lat' => (float) $row->$latCol,
                'lng' => (float) $row->$lngCol,
                'name' => e($this->val($row, [$nameCol], 'Destination')),
            ];
        }
        return $items;
    }

    private function cols(string $table): array
    {
        if (! isset(self::$colCache[$table])) {
            self::$colCache[$table] = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        }
        return self::$colCache[$table];
    }

    private function col(string $table, array $candidates): ?string
    {
        $have = $this->cols($table);
        foreach ($candidates as $c) {
            if (in_array($c, $have, true)) return $c;
        }
        return null;
    }

    private function val($row, array $keys, $default = null)
    {
        foreach ($keys as $k) {
            if (! $k) continue;
            $v = is_array($row) ? ($row[$k] ?? null) : ($row->$k ?? null);
            if ($v !== null && $v !== '') return $v;
        }
        return $default;
    }

    private function userName($id): string
    {
        if (! $id) return 'A traveler';
        try {
            $name = DB::table('users')->where('id', $id)->value('name');
            return $name ? (string) $name : 'A traveler';
        } catch (\Throwable $e) {
            return 'A traveler';
        }
    }

    private function timeAgo(int $ts): string
    {
        if ($ts <= 0) return '';
        try {
            return Carbon::createFromTimestamp($ts)->diffForHumans();
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function stamp($value): string
    {
        try {
            return Carbon::parse($value)->format('d M · H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}