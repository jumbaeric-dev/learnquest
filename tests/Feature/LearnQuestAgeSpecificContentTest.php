<?php

namespace Tests\Unit;

use Database\Seeders\LearnQuestAgeSpecificContent;
use Database\Seeders\LearnQuestCurriculumSeeder;
use ReflectionClass;
use RuntimeException;
use Tests\TestCase;

class LearnQuestAgeSpecificContentTest extends TestCase
{
    private function curriculumDefinitions(): array
    {
        $reflection = new ReflectionClass(LearnQuestCurriculumSeeder::class);

        return [
            $reflection
                ->getReflectionConstant('WORLDS')
                ->getValue(),
            array_keys(
                $reflection
                    ->getReflectionConstant('AGE_BANDS')
                    ->getValue()
            ),
        ];
    }

    public function test_complete_curriculum_has_valid_age_specific_coverage(): void
    {
        [$worlds, $ageGroups] = $this->curriculumDefinitions();

        LearnQuestAgeSpecificContent::validateCoverage(
            $worlds,
            $ageGroups
        );

        $this->assertTrue(true);
    }

    public function test_missing_course_description_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Missing age-specific course description'
        );

        LearnQuestAgeSpecificContent::courseDescription(
            'unknown-world',
            '3-5'
        );
    }

    public function test_missing_lesson_age_variant_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Missing age-specific content'
        );

        LearnQuestAgeSpecificContent::lessonDefinitionForAge(
            'self-discovery',
            ['title' => 'Unknown Lesson'],
            '6-9'
        );
    }

    public function test_curriculum_lesson_without_registered_content_is_rejected(): void
    {
        [$worlds, $ageGroups] = $this->curriculumDefinitions();

        $worlds['self-discovery']['courses'][0]['modules'][0]['lessons'][0]['title']
            = 'Unregistered Test Lesson';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Missing age-specific content for self-discovery/Unregistered Test Lesson/3-5.'
        );

        LearnQuestAgeSpecificContent::validateCoverage(
            $worlds,
            $ageGroups
        );
    }

    public function test_registered_lesson_missing_from_curriculum_is_rejected(): void
    {
        [$worlds, $ageGroups] = $this->curriculumDefinitions();

        array_shift(
            $worlds['self-discovery']['courses'][0]['modules'][0]['lessons']
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Age-specific content registry does not exactly match the curriculum.'
        );

        LearnQuestAgeSpecificContent::validateCoverage(
            $worlds,
            $ageGroups
        );
    }

    public function test_lesson_definition_preserves_metadata_and_applies_age_specific_content(): void
    {
        $definition = [
            'title' => 'What Makes Me Unique?',
            'skill' => 'Self-Awareness',
            'custom_metadata' => 'preserve-me',
        ];

        $result = LearnQuestAgeSpecificContent::lessonDefinitionForAge(
            'self-discovery',
            $definition,
            '3-5'
        );

        $this->assertSame($definition['title'], $result['title']);
        $this->assertSame($definition['skill'], $result['skill']);
        $this->assertSame(
            'preserve-me',
            $result['custom_metadata']
        );

        $this->assertIsString($result['story']);
        $this->assertNotSame('', trim($result['story']));

        $this->assertIsString($result['question']);
        $this->assertNotSame('', trim($result['question']));

        $this->assertIsArray($result['options']);
        $this->assertGreaterThanOrEqual(2, count($result['options']));

        $this->assertIsInt($result['answer']);
        $this->assertArrayHasKey($result['answer'], $result['options']);
    }
}