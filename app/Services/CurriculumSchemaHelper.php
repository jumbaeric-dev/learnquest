<?php

namespace App\Services;

use Illuminate\Support\Str;

class CurriculumSchemaHelper
{
    /**
     * @return '3-5'|'6-9'|'10-14'
     */
    public static function normalizeAgeGroup(?string $ageGroup): string
    {
        $allowed = ['3-5', '6-9', '10-14'];

        if ($ageGroup !== null && in_array($ageGroup, $allowed, true)) {
            return $ageGroup;
        }

        return '6-9';
    }

    /**
     * Map free-form AI activity types to database enum values.
     */
    public static function normalizeActivityType(string $type): string
    {
        return match ($type) {
            'video' => 'video',
            'story', 'text' => 'story',
            'quiz' => 'quiz',
            'audio' => 'audio',
            'drag_drop', 'interactive' => 'drag_drop',
            'drawing' => 'drawing',
            'flashcard' => 'flashcard',
            'ai_chat' => 'ai_chat',
            'coding_challenge' => 'coding_challenge',
            'project' => 'project',
            default => 'story',
        };
    }

    /**
     * @param  mixed  $content
     * @return array<string, mixed>
     */
    public static function normalizeActivityContent(mixed $content, string $activityType): array
    {
        if (is_array($content)) {
            return $content;
        }

        if (! is_string($content) || $content === '') {
            return [];
        }

        return match ($activityType) {
            'story' => ['story_text' => $content],
            'quiz' => ['question' => $content],
            default => ['body' => $content],
        };
    }

    public static function uniqueCourseSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (\App\Models\Course::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
