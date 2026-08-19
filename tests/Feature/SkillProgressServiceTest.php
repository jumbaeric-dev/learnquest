<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ChildSkillProgress;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Services\Progress\SkillProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SkillProgressService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SkillProgressService::class);
    }

    public function test_skill_progress_is_created_when_xp_is_awarded(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->awardXp(
            $child,
            $skill,
            25
        );

        $this->assertInstanceOf(
            ChildSkillProgress::class,
            $progress
        );

        $this->assertSame(25, $progress->xp);
        $this->assertSame(1, $progress->level);
        $this->assertSame(
            2.5,
            (float) $progress->progress_percentage
        );

        $this->assertDatabaseHas(
            'child_skill_progress',
            [
                'child_id' => $child->id,
                'skill_id' => $skill->id,
                'xp' => 25,
            ]
        );
    }

    public function test_skill_xp_is_accumulated(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skill,
            40
        );

        $progress = $this->service->awardXp(
            $child,
            $skill,
            30
        );

        $this->assertSame(70, $progress->xp);
        $this->assertSame(1, $progress->level);
        $this->assertSame(
            7.0,
            (float) $progress->progress_percentage
        );

        $this->assertDatabaseCount(
            'child_skill_progress',
            1
        );
    }

    public function test_skill_level_increases_using_configured_xp_per_level(): void
    {
        config([
            'learnquest.xp_per_level' => 100,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->awardXp(
            $child,
            $skill,
            100
        );

        $this->assertSame(100, $progress->xp);
        $this->assertSame(2, $progress->level);
        $this->assertSame(
            10.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_skill_progress_percentage_uses_max_skill_xp(): void
    {
        config([
            'learnquest.max_skill_xp' => 1000,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->awardXp(
            $child,
            $skill,
            250
        );

        $this->assertSame(250, $progress->xp);
        $this->assertSame(
            25.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_skill_xp_is_capped_at_max_skill_xp(): void
    {
        config([
            'learnquest.max_skill_xp' => 1000,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->awardXp(
            $child,
            $skill,
            1200
        );

        $this->assertSame(1000, $progress->xp);
        $this->assertSame(
            100.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_skill_progress_percentage_never_exceeds_100(): void
    {
        config([
            'learnquest.max_skill_xp' => 1000,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->awardXp(
            $child,
            $skill,
            1000
        );

        $this->assertSame(
            100.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_get_progress_returns_existing_skill_progress(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skill,
            50
        );

        $progress = $this->service->getProgress(
            $child,
            $skill
        );

        $this->assertNotNull($progress);
        $this->assertSame(50, $progress->xp);
    }

    public function test_get_progress_returns_null_when_no_progress_exists(): void
    {
        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $progress = $this->service->getProgress(
            $child,
            $skill
        );

        $this->assertNull($progress);
    }

    public function test_is_mastered_returns_false_before_max_skill_xp(): void
    {
        config([
            'learnquest.max_skill_xp' => 1000,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skill,
            999
        );

        $this->assertFalse(
            $this->service->isMastered(
                $child,
                $skill
            )
        );
    }

    public function test_is_mastered_returns_true_at_max_skill_xp(): void
    {
        config([
            'learnquest.max_skill_xp' => 1000,
        ]);

        $child = Child::factory()->create();

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skill,
            1000
        );

        $this->assertTrue(
            $this->service->isMastered(
                $child,
                $skill
            )
        );
    }

    public function test_skill_progress_is_isolated_between_children(): void
    {
        $childOne = Child::factory()->create();
        $childTwo = Child::factory()->create();

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $childOne,
            $skill,
            100
        );

        $this->service->awardXp(
            $childTwo,
            $skill,
            50
        );

        $progressOne = $this->service->getProgress(
            $childOne,
            $skill
        );

        $progressTwo = $this->service->getProgress(
            $childTwo,
            $skill
        );

        $this->assertSame(100, $progressOne->xp);
        $this->assertSame(50, $progressTwo->xp);

        $this->assertDatabaseCount(
            'child_skill_progress',
            2
        );
    }

    public function test_skill_progress_is_isolated_between_skills(): void
    {
        $child = Child::factory()->create();

        $skillOne = Skill::factory()->create();
        $skillTwo = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skillOne,
            100
        );

        $progressOne = $this->service->getProgress(
            $child,
            $skillOne
        );

        $progressTwo = $this->service->getProgress(
            $child,
            $skillTwo
        );

        $this->assertSame(100, $progressOne->xp);
        $this->assertNull($progressTwo);
    }

    public function test_awarding_skill_xp_does_not_award_child_xp(): void
    {
        $child = Child::factory()->create([
            'xp' => 0,
        ]);

        $skill = Skill::factory()->create();

        $this->service->awardXp(
            $child,
            $skill,
            100
        );

        $this->assertSame(
            0,
            $child->fresh()->xp
        );
    }
}