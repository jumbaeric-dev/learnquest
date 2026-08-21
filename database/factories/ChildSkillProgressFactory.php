<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\ChildSkillProgress;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChildSkillProgressFactory extends Factory
{
    protected $model = ChildSkillProgress::class;

    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'skill_id' => Skill::factory(),
            'xp' => 0,
            'level' => 1,
            'progress_percentage' => 0,
        ];
    }
}
