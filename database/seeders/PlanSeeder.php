<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanPrice;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['key' => 'free'], [
            'name' => 'Explorer',
            'tagline' => 'Everything your child needs to start exploring.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $premium = Plan::updateOrCreate(['key' => 'premium'], [
            'name' => 'Family Premium',
            'tagline' => 'The full LearnQuest universe for the whole family.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        /*
         * PLACEHOLDER PRICES in minor units (cents). Tune after launch
         * testing. Re-running this seeder resets prices to these values.
         */
        $prices = [
            ['KES', 1, 49900],
            ['KES', 3, 129900],
            ['KES', 12, 449900],
            ['USD', 1, 499],
            ['USD', 3, 1299],
            ['USD', 12, 4499],
        ];

        foreach ($prices as [$currency, $months, $amount]) {
            PlanPrice::updateOrCreate(
                [
                    'plan_id' => $premium->id,
                    'currency' => $currency,
                    'months' => $months,
                ],
                [
                    'amount_minor' => $amount,
                    'is_active' => true,
                ]
            );
        }
    }
}