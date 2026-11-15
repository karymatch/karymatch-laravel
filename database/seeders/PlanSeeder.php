<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['slug' => 'gratis', 'name' => 'Gratis', 'price_month' => 0,
             'max_active_ads' => 5, 'max_screening_questions' => 3,
             'max_daily_invites' => 5, 'ad_duration_days' => 15],
            ['slug' => 'bisnis', 'name' => 'Bisnis', 'price_month' => 249000,
             'max_active_ads' => 20, 'max_screening_questions' => 5,
             'max_daily_invites' => 30, 'ad_duration_days' => 45],
            ['slug' => 'pro', 'name' => 'Pro', 'price_month' => 599000,
             'max_active_ads' => null, 'max_screening_questions' => 10,
             'max_daily_invites' => 100, 'ad_duration_days' => 60],
        ];

        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
