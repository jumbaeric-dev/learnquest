<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Services\CurriculumSchemaHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurriculumSchemaHelperTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_normalizes_age_groups(): void
    {
        $this->assertSame('6-9', CurriculumSchemaHelper::normalizeAgeGroup('6-9'));
        $this->assertSame('6-9', CurriculumSchemaHelper::normalizeAgeGroup('invalid'));
        $this->assertSame('6-9', CurriculumSchemaHelper::normalizeAgeGroup(null));
    }

    public function test_it_normalizes_activity_types(): void
    {
        $this->assertSame('story', CurriculumSchemaHelper::normalizeActivityType('text'));
        $this->assertSame('drag_drop', CurriculumSchemaHelper::normalizeActivityType('interactive'));
        $this->assertSame('quiz', CurriculumSchemaHelper::normalizeActivityType('quiz'));
        $this->assertSame('story', CurriculumSchemaHelper::normalizeActivityType('unknown'));
    }

    public function test_it_generates_unique_course_slugs(): void
    {
        Course::factory()->create(['slug' => 'ai-basics']);

        $this->assertSame(
            'ai-basics-1',
            CurriculumSchemaHelper::uniqueCourseSlug('AI Basics')
        );
    }
}
