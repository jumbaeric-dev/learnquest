<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\ChildSkillProgress;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class ChildSkillProgressSeeder extends Seeder
{
    public function run(): void
    {
        $skills = Skill::all();

        if ($skills->isEmpty()) {
            $this->command->warn('Seed Skills first.');

            return;
        }

        foreach (Child::all() as $child) {

            /*
             |----------------------------------------------------------
             | Each child gets a different learning personality
             |----------------------------------------------------------
             */

            $profile = rand(1, 5);

            foreach ($skills as $skill) {

                switch ($profile) {

                    // AI Enthusiast
                    case 1:

                        $xp = match ($skill->name) {
                            'AI Literacy' => rand(850, 1000),
                            'Coding' => rand(500, 700),
                            default => rand(200, 600),
                        };

                        break;

                    // Future Programmer
                    case 2:

                        $xp = match ($skill->name) {
                            'Coding' => rand(850, 1000),
                            'AI Literacy' => rand(500, 700),
                            default => rand(250, 650),
                        };

                        break;

                    // Creative Genius
                    case 3:

                        $xp = match ($skill->name) {
                            'Creativity' => rand(850, 1000),
                            default => rand(300, 700),
                        };

                        break;

                    // Critical Thinker
                    case 4:

                        $xp = match ($skill->name) {
                            'Critical Thinking' => rand(850, 1000),
                            default => rand(300, 700),
                        };

                        break;

                    // Balanced Learner
                    default:

                        $xp = rand(500, 850);

                        break;
                }

                $level = floor($xp / 100) + 1;

                ChildSkillProgress::create([

                    'child_id' => $child->id,

                    'skill_id' => $skill->id,

                    'xp' => $xp,

                    'level' => $level,

                    'progress_percentage' => min(
                        round(($xp / 1000) * 100),
                        100
                    ),

                ]);
            }
        }
    }
}