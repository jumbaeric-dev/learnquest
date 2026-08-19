<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ChildSkillProgress;
use App\Models\Skill;
use App\Services\FutureReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FutureReadinessServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_child_has_zero_readiness_score(): void
    {
        $child = Child::factory()->create();

        $this->assertSame(
            0.0,
            FutureReadinessService::calculateScore($child)
        );
    }

    public function test_readiness_score_is_average_of_skill_progress(): void
    {
        $child = Child::factory()->create();

        $skillA = Skill::factory()->create();
        $skillB = Skill::factory()->create();

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skillA->id,
            'xp' => 100,
            'level' => 2,
            'progress_percentage' => 40,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skillB->id,
            'xp' => 200,
            'level' => 3,
            'progress_percentage' => 80,
        ]);

        $this->assertSame(
            60.0,
            FutureReadinessService::calculateScore($child->fresh())
        );
    }

    public function test_readiness_score_is_rounded_to_two_decimal_places(): void
    {
        $child = Child::factory()->create();

        $skillA = Skill::factory()->create();
        $skillB = Skill::factory()->create();
        $skillC = Skill::factory()->create();

        foreach (
            [
                [$skillA, 10],
                [$skillB, 20],
                [$skillC, 30],
            ] as [$skill, $progress]
        ) {
            ChildSkillProgress::factory()->create([
                'child_id' => $child->id,
                'skill_id' => $skill->id,
                'progress_percentage' => $progress,
            ]);
        }

        $this->assertSame(
            20.0,
            FutureReadinessService::calculateScore($child->fresh())
        );
    }

    public function test_level_is_explorer_when_no_configured_level_matches(): void
    {
        $child = Child::factory()->create();

        config([
            'learnquest.future_readiness.levels' => [
                [
                    'min_score' => 50,
                    'title' => 'Builder',
                ],
            ],
        ]);

        $this->assertSame(
            'Explorer',
            FutureReadinessService::calculateLevel($child)
        );
    }

    public function test_level_uses_highest_matching_configured_threshold(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skill->id,
            'progress_percentage' => 75,
        ]);

        config([
            'learnquest.future_readiness.levels' => [
                [
                    'min_score' => 0,
                    'title' => 'Explorer',
                ],
                [
                    'min_score' => 50,
                    'title' => 'Builder',
                ],
                [
                    'min_score' => 70,
                    'title' => 'Future Ready',
                ],
            ],
        ]);

        $this->assertSame(
            'Future Ready',
            FutureReadinessService::calculateLevel($child->fresh())
        );
    }

    public function test_strongest_skills_are_ordered_by_xp_descending(): void
    {
        $child = Child::factory()->create();

        $low = Skill::factory()->create([
            'name' => 'Low Skill',
        ]);

        $high = Skill::factory()->create([
            'name' => 'High Skill',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $low->id,
            'xp' => 20,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $high->id,
            'xp' => 100,
        ]);

        $result = FutureReadinessService::strongestSkills(
            $child->fresh(),
            1
        );

        $this->assertCount(1, $result);
        $this->assertSame(
            'High Skill',
            $result->first()->skill->name
        );
    }

    public function test_weakest_skills_are_ordered_by_xp_ascending(): void
    {
        $child = Child::factory()->create();

        $low = Skill::factory()->create([
            'name' => 'Low Skill',
        ]);

        $high = Skill::factory()->create([
            'name' => 'High Skill',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $low->id,
            'xp' => 20,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $high->id,
            'xp' => 100,
        ]);

        $result = FutureReadinessService::weakestSkills(
            $child->fresh(),
            1
        );

        $this->assertCount(1, $result);
        $this->assertSame(
            'Low Skill',
            $result->first()->skill->name
        );
    }

    public function test_strongest_and_weakest_skill_helpers_return_names(): void
    {
        $child = Child::factory()->create();

        $weak = Skill::factory()->create([
            'name' => 'Weak Skill',
        ]);

        $strong = Skill::factory()->create([
            'name' => 'Strong Skill',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $weak->id,
            'xp' => 10,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $strong->id,
            'xp' => 100,
        ]);

        $freshChild = $child->fresh();

        $this->assertSame(
            'Strong Skill',
            FutureReadinessService::strongestSkill($freshChild)
        );

        $this->assertSame(
            'Weak Skill',
            FutureReadinessService::weakestSkill($freshChild)
        );
    }

    public function test_radar_data_contains_skill_names_and_progress_values(): void
    {
        $child = Child::factory()->create();

        $skillA = Skill::factory()->create([
            'name' => 'Coding',
        ]);

        $skillB = Skill::factory()->create([
            'name' => 'Communication',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skillA->id,
            'progress_percentage' => 40,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skillB->id,
            'progress_percentage' => 75,
        ]);

        $result = FutureReadinessService::radarData(
            $child->fresh()
        );

        $this->assertSame(
            ['Coding', 'Communication'],
            $result['labels']
        );

        $this->assertSame(
            [40.0, 75.0],
            $result['values']
        );
    }

    public function test_build_returns_complete_future_readiness_profile(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create([
            'name' => 'Coding',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skill->id,
            'xp' => 100,
            'level' => 2,
            'progress_percentage' => 60,
        ]);

        $result = FutureReadinessService::build(
            $child->fresh()
        );

        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('skills', $result);
        $this->assertArrayHasKey('strongest_skills', $result);
        $this->assertArrayHasKey('weakest_skills', $result);
        $this->assertArrayHasKey('radar_data', $result);

        $this->assertSame(60.0, $result['overall_score']);

        $this->assertSame(
            'Coding',
            $result['skills']->first()['skill']
        );
    }

    public function test_build_respects_configured_skill_limits(): void
    {
        $child = Child::factory()->create();

        foreach (range(1, 6) as $index) {
            $skill = Skill::factory()->create();

            ChildSkillProgress::factory()->create([
                'child_id' => $child->id,
                'skill_id' => $skill->id,
                'xp' => $index * 10,
                'progress_percentage' => $index * 10,
            ]);
        }

        config([
            'learnquest.future_readiness.strongest_skills_limit' => 2,
            'learnquest.future_readiness.weakest_skills_limit' => 3,
        ]);

        $result = FutureReadinessService::build(
            $child->fresh()
        );

        $this->assertCount(
            2,
            $result['strongest_skills']
        );

        $this->assertCount(
            3,
            $result['weakest_skills']
        );
    }

    public function test_profile_returns_core_learner_profile(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create([
            'name' => 'Coding',
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $child->id,
            'skill_id' => $skill->id,
            'xp' => 50,
            'progress_percentage' => 50,
        ]);

        $result = FutureReadinessService::profile(
            $child->fresh()
        );

        $this->assertSame(50.0, $result['score']);
        $this->assertArrayHasKey('level', $result);
        $this->assertArrayHasKey('strongest_skills', $result);
        $this->assertArrayHasKey('weakest_skills', $result);
        $this->assertArrayHasKey('radar_data', $result);
    }

    public function test_platform_average_returns_zero_when_there_are_no_children(): void
    {
        $this->assertSame(
            0.0,
            FutureReadinessService::platformAverage()
        );
    }

    public function test_platform_average_averages_child_readiness_scores(): void
    {
        $childA = Child::factory()->create();
        $childB = Child::factory()->create();

        $skillA = Skill::factory()->create();
        $skillB = Skill::factory()->create();

        ChildSkillProgress::factory()->create([
            'child_id' => $childA->id,
            'skill_id' => $skillA->id,
            'progress_percentage' => 20,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $childB->id,
            'skill_id' => $skillB->id,
            'progress_percentage' => 80,
        ]);

        $this->assertSame(
            50.0,
            FutureReadinessService::platformAverage()
        );
    }

    public function test_top_learners_are_ordered_by_readiness_score(): void
    {
        $low = Child::factory()->create();
        $high = Child::factory()->create();

        $lowSkill = Skill::factory()->create();
        $highSkill = Skill::factory()->create();

        ChildSkillProgress::factory()->create([
            'child_id' => $low->id,
            'skill_id' => $lowSkill->id,
            'progress_percentage' => 20,
        ]);

        ChildSkillProgress::factory()->create([
            'child_id' => $high->id,
            'skill_id' => $highSkill->id,
            'progress_percentage' => 90,
        ]);

        $result = FutureReadinessService::topLearners(1);

        $this->assertCount(1, $result);
        $this->assertSame(
            $high->id,
            $result->first()->id
        );
    }

    public function test_top_learners_respects_limit(): void
    {
        Child::factory()->count(5)->create();

        $result = FutureReadinessService::topLearners(3);

        $this->assertCount(3, $result);
    }
}