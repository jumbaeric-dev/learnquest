<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChildrenSeeder extends Seeder
{
    public function run(): void
    {
        $parents = User::all();

        if ($parents->isEmpty()) {
            $this->command->warn('No users found. Seed users first.');

            return;
        }

        $children = [

            ['Emma', 'Johnson'],
            ['Liam', 'Williams'],
            ['Olivia', 'Brown'],
            ['Noah', 'Jones'],
            ['Ava', 'Garcia'],
            ['Sophia', 'Miller'],
            ['James', 'Davis'],
            ['Isabella', 'Wilson'],
            ['Lucas', 'Moore'],
            ['Mia', 'Taylor'],

            ['Benjamin', 'Anderson'],
            ['Charlotte', 'Thomas'],
            ['Henry', 'Jackson'],
            ['Amelia', 'White'],
            ['Elijah', 'Harris'],
            ['Harper', 'Martin'],
            ['Daniel', 'Thompson'],
            ['Evelyn', 'Martinez'],
            ['Michael', 'Robinson'],
            ['Abigail', 'Clark'],

            ['Alexander', 'Lewis'],
            ['Emily', 'Lee'],
            ['Samuel', 'Walker'],
            ['Ella', 'Hall'],
            ['David', 'Allen'],
            ['Scarlett', 'Young'],
            ['Joseph', 'King'],
            ['Grace', 'Wright'],
            ['Matthew', 'Scott'],
            ['Chloe', 'Green'],

        ];

        foreach ($children as $child) {

            $xp = rand(50, 5000);

            Child::create([

                'parent_id' => $parents->random()->id,

                'first_name' => $child[0],

                'last_name' => $child[1],

                'date_of_birth' => now()->subYears(rand(6, 14))
                    ->subDays(rand(0, 365)),

                'avatar' => null,

                'xp' => $xp,

                'level' => floor($xp / 100) + 1,

                'is_active' => rand(1, 100) <= 95,

            ]);
        }
    }
}