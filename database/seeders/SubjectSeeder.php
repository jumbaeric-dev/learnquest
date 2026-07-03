<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            'Math',
            'Reading',
            'Science',
            'Coding',
            'Art',
            'Bible Stories',
            'Life Skills',
            'AI for Kids',
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate([
                'slug' => Str::slug($subject),
            ], [
                'name' => $subject,
                'is_active' => true,
            ]);
        }
    }
}
