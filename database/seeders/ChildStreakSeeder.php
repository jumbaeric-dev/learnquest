<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\ChildStreak;
use Illuminate\Database\Seeder;

class ChildStreakSeeder extends Seeder
{
    public function run(): void
    {
        Child::all()->each(function ($child) {

            ChildStreak::firstOrCreate(
                [
                    'child_id' => $child->id,
                ],
                [
                    'current_streak' => 0,
                    'longest_streak' => 0,
                ]
            );

        });
    }
}