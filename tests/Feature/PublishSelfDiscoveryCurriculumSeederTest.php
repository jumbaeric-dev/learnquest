<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Subject;
use Database\Seeders\LearnQuestCurriculumSeeder;
use Database\Seeders\PublishSelfDiscoveryCurriculumSeeder;
use Database\Seeders\SkillSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PublishSelfDiscoveryCurriculumSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SubjectSeeder::class);
        $this->seed(SkillSeeder::class);
        $this->seed(LearnQuestCurriculumSeeder::class);
    }

    public function test_valid_self_discovery_curriculum_is_published_for_all_three_age_bands(): void
    {
        $subject = Subject::where('slug', 'self-discovery')->firstOrFail();

        $this->assertSame(
            3,
            Course::where('subject_id', $subject->id)->count()
        );

        $this->assertSame(
            0,
            Course::where('subject_id', $subject->id)
                ->where('is_published', true)
                ->count()
        );

        (new PublishSelfDiscoveryCurriculumSeeder())->run();

        $publishedCourses = Course::where('subject_id', $subject->id)
            ->where('is_published', true)
            ->get();

        $this->assertCount(3, $publishedCourses);

        $this->assertSame(
            12,
            Lesson::whereHas('module', function ($query) use ($publishedCourses) {
                $query->whereIn(
                    'course_id',
                    $publishedCourses->pluck('id')
                );
            })
                ->where('is_published', true)
                ->count()
        );

        $this->assertSame(
            24,
            LessonActivity::where('is_published', true)
                ->whereHas('lesson.module', function ($query) use ($subject) {
                    $query->whereHas('course', function ($courseQuery) use ($subject) {
                        $courseQuery->where('subject_id', $subject->id);
                    });
                })
                ->count()
        );

        $this->assertSame(
            0,
            Course::where('subject_id', '!=', $subject->id)
                ->where('is_published', true)
                ->count()
        );

        $this->assertSame(
            36,
            Course::count()
        );
    }

    public function test_invalid_story_content_stops_publication_without_publishing_any_records(): void
    {
        $story = LessonActivity::query()
            ->get()
            ->first(function (LessonActivity $activity): bool {
                return is_array($activity->content)
                    && array_key_exists('instructions', $activity->content);
            });

        $this->assertNotNull(
            $story,
            'Expected to find a story activity containing instructions.'
        );

        $content = $story->content;
        $content['instructions'] = '';
        $story->content = $content;
        $story->save();

        try {
            (new PublishSelfDiscoveryCurriculumSeeder())->run();

            $this->fail(
                'Invalid story content should prevent curriculum publication.'
            );
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString(
                'Self-Discovery publication stopped:',
                $exception->getMessage()
            );
        }

        $this->assertSame(
            0,
            Course::where('is_published', true)->count()
        );

        $this->assertSame(
            0,
            Lesson::where('is_published', true)->count()
        );

        $this->assertSame(
            0,
            LessonActivity::where('is_published', true)->count()
        );
    }

    public function test_running_publication_seeder_twice_does_not_duplicate_published_content(): void
    {
        (new PublishSelfDiscoveryCurriculumSeeder())->run();
        (new PublishSelfDiscoveryCurriculumSeeder())->run();

        $subject = Subject::where('slug', 'self-discovery')->firstOrFail();

        $this->assertSame(
            3,
            Course::where('subject_id', $subject->id)
                ->where('is_published', true)
                ->count()
        );

        $this->assertSame(
            12,
            Lesson::where('is_published', true)
                ->whereHas('module.course', function ($query) use ($subject) {
                    $query->where('subject_id', $subject->id);
                })
                ->count()
        );

        $this->assertSame(
            24,
            LessonActivity::where('is_published', true)
                ->whereHas('lesson.module.course', function ($query) use ($subject) {
                    $query->where('subject_id', $subject->id);
                })
                ->count()
        );

        $this->assertSame(36, Course::count());
    }
}
