<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\Subject;
use App\Services\Child\Learning\ChildAgeBandService;
use App\Services\Child\Learning\LearningContentAccessService;
use Database\Seeders\LearnQuestCurriculumSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ChildAgeBandAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);
        $this->seed(LearnQuestCurriculumSeeder::class);
    }

    public function test_age_band_service_matches_the_childs_age(): void
    {
        $service = app(ChildAgeBandService::class);

        $youngChild = Child::factory()->create([
            'date_of_birth' => now()->subYears(4)->toDateString(),
        ]);

        $middleChild = Child::factory()->create([
            'date_of_birth' => now()->subYears(8)->toDateString(),
        ]);

        $olderChild = Child::factory()->create([
            'date_of_birth' => now()->subYears(12)->toDateString(),
        ]);

        $this->assertSame('3-5', $service->ageGroupFor($youngChild));
        $this->assertSame('6-9', $service->ageGroupFor($middleChild));
        $this->assertSame('10-14', $service->ageGroupFor($olderChild));
    }

    public function test_child_cannot_open_a_canonical_course_for_another_age_band(): void
    {
        $youngChild = Child::factory()->create([
            'date_of_birth' => now()->subYears(4)->toDateString(),
        ]);

        $olderChild = Child::factory()->create([
            'date_of_birth' => now()->subYears(8)->toDateString(),
        ]);

        $course = Course::query()
            ->where('age_group', '3-5')
            ->whereHas(
                'subject',
                fn ($query) => $query->where('slug', 'self-discovery')
            )
            ->firstOrFail();

        $course->is_published = true;
        $course->save();

        $access = app(LearningContentAccessService::class);

        $this->assertSame(
            $course->id,
            $access->course($course, $youngChild)->id
        );

        try {
            $access->course($course, $olderChild);
            $this->fail('A child should not access a course for another age band.');
        } catch (NotFoundHttpException) {
            $this->assertTrue(true);
        }
    }

    public function test_only_canonical_worlds_remain_active(): void
    {
        $expected = [
            'self-discovery',
            'growth-habits',
            'people-relationships',
            'critical-thinking',
            'digital-life-safety',
            'money-financial-wisdom',
            'entrepreneurship-innovation',
            'ai-future-intelligence',
            'coding-robotics-engineering',
            'science-nature-universe',
            'healthy-practical-life',
            'leadership-citizenship-global-futures',
        ];

        sort($expected);

        $active = Subject::query()
            ->where('is_active', true)
            ->pluck('slug')
            ->sort()
            ->values()
            ->all();

        $this->assertSame($expected, $active);
    }
}