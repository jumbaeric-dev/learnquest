<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LearnQuestWorldContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MoneyFinancialWisdomWorldSeeder::class,
        ]);
    }
}