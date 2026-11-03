<?php

namespace Tests\Feature;

use App\Models\ActivityProgress;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Subject;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    private const WORLDS = [
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

    public function test_master_seeder_builds_canonical_curriculum_and_preserves_legacy_course(): void
    {
        $this->seed(DatabaseSeeder::class);

        $courses = Course::query()->whereHas(
            'subject',
            fn ($query) => $query->whereIn('slug', self::WORLDS)
        );

        $lessons = Lesson::query()->whereHas(
            'module.course.subject',
            fn ($query) => $query->whereIn('slug', self::WORLDS)
        );

        $activities = LessonActivity::query()->whereHas(
            'lesson.module.course.subject',
            fn ($query) => $query->whereIn('slug', self::WORLDS)
        );

        $this->assertSame(36, (clone $courses)->count());
        $this->assertSame(0, (clone $courses)->where('is_published', true)->count());
        $this->assertSame(144, $lessons->count());
        $this->assertSame(288, $activities->count());

        $this->assertSame(
            0,
            $activities->whereNotIn('activity_type', ['story', 'quiz'])->count()
        );

        $legacySubject = Subject::query()
            ->where('slug', 'ai-for-kids')
            ->firstOrFail();

        $legacyCourse = Course::query()
            ->where('slug', 'ai-for-kids-course')
            ->firstOrFail();

        $this->assertFalse((bool) $legacySubject->is_active);
        $this->assertFalse((bool) $legacyCourse->is_published);

        $this->assertSame(
            3,
            Lesson::query()
                ->whereHas(
                    'module.course',
                    fn ($query) => $query->where('slug', 'ai-for-kids-course')
                )
                ->count()
        );

        $this->assertSame(
            6,
            LessonActivity::query()
                ->whereHas(
                    'lesson.module.course',
                    fn ($query) => $query->where('slug', 'ai-for-kids-course')
                )
                ->count()
        );

        // Do not invent completion records for unpublished activities.
        $this->assertSame(0, ActivityProgress::query()->count());
    }
}