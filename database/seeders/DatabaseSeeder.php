<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['Adventure', 'fa-mountain'], ['Beach', 'fa-umbrella-beach'], ['Culture', 'fa-landmark'],
            ['Food', 'fa-utensils'], ['Nature', 'fa-tree'], ['Nightlife', 'fa-martini-glass'],
            ['Shopping', 'fa-bag-shopping'], ['History', 'fa-book-open'], ['Photography', 'fa-camera'],
            ['Wellness', 'fa-spa'], ['Music', 'fa-music'], ['Sports', 'fa-futbol'],
        ];
        foreach ($interests as [$name, $icon]) {
            Interest::firstOrCreate(['name' => $name], ['icon' => $icon]);
        }
    }
}