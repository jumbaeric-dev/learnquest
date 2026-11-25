<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorldSkillsSeeder extends Seeder
{
    /**
     * Extra skills for the worlds added on top of SkillSeeder.
     * Safe to run repeatedly (keyed by slug, like SkillSeeder).
     */
    private const SKILLS = [
        // Math
        'Number Sense' => 'Understand numbers, counting, place value, and how numbers relate to each other.',
        'Addition and Subtraction' => 'Add and subtract numbers to solve everyday problems.',
        'Multiplication and Division' => 'Use equal groups, sharing, and repeated addition to multiply and divide.',
        'Fractions and Parts' => 'Understand halves, quarters, and other equal parts of a whole.',
        'Measurement and Data' => 'Measure length, time, and amounts, and read simple charts.',
        'Shapes and Spatial Reasoning' => 'Recognise shapes, patterns, and positions in space.',
        'Mathematical Reasoning' => 'Explain why an answer makes sense and check it.',

        // Reading and communication
        'Phonics and Decoding' => 'Connect letters to sounds and read new words.',
        'Vocabulary Building' => 'Learn new words and use them correctly.',
        'Reading Comprehension' => 'Understand, remember, and think about what is read.',
        'Writing and Composition' => 'Organise ideas into clear sentences and short pieces of writing.',
        'Storytelling' => 'Create stories with characters, settings, and a beginning, middle, and end.',

        // Money
        'Earning and Work' => 'Understand how people earn money through useful work.',
        'Generosity and Sharing' => 'Practise sharing time, kindness, and resources to help others.',

        // Coding
        'Loops and Repetition' => 'Use repeated steps to make instructions shorter and clearer.',
        'Conditions and Logic' => 'Use if-then thinking to make choices in instructions.',
        'Variables and Data' => 'Store and change information that a program uses.',
        'Functions and Reuse' => 'Group steps into reusable parts.',

        // AI
        'Prompting Skills' => 'Write clear, safe, and specific instructions for AI tools.',
        'Machine Learning Basics' => 'Understand that computers can learn patterns from examples.',

        // Feelings
        'Calming Strategies' => 'Use simple strategies to settle strong feelings and make a plan.',

        // Science
        'Life Science' => 'Learn how plants, animals, and people live, grow, and change.',
        'Earth and Space' => 'Explore weather, land, water, the Sun, the Moon, and planets.',
        'Matter and Forces' => 'Investigate materials, motion, and the push and pull of forces.',
    ];

    public function run(): void
    {
        foreach (self::SKILLS as $name => $description) {
            Skill::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('World skills seeded or updated: ' . count(self::SKILLS) . '.');
    }
}