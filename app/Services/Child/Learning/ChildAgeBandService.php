<?php

namespace App\Services\Child\Learning;

use App\Models\Child;
use App\Models\Course;
use Carbon\CarbonImmutable;

class ChildAgeBandService
{
    public const CANONICAL_WORLD_SLUGS = [
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

    public function ageGroupFor(Child $child): ?string
    {
        if (! $child->date_of_birth) {
            return null;
        }

        $age = CarbonImmutable::parse($child->date_of_birth)->age;

        return match (true) {
            $age >= 3 && $age <= 5 => '3-5',
            $age >= 6 && $age <= 9 => '6-9',
            $age >= 10 && $age <= 14 => '10-14',
            default => null,
        };
    }

    public function isCanonicalWorld(?string $slug): bool
    {
        return $slug !== null
            && in_array($slug, self::CANONICAL_WORLD_SLUGS, true);
    }

    public function allowsCourse(Course $course, Child $child): bool
    {
        $course->loadMissing('subject');

        // The new age-band policy applies to the canonical curriculum.
        if (! $this->isCanonicalWorld($course->subject?->slug)) {
            return true;
        }

        $ageGroup = $this->ageGroupFor($child);

        return $ageGroup !== null
            && $course->age_group === $ageGroup;
    }
}