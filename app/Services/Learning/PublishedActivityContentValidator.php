<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PublishedActivityContentValidator
{
    /**
     * Activity types currently supported by the learner-facing activity page.
     * Types without a complete learner experience may be saved as drafts,
     * but must not be published yet.
     *
     * @var list<string>
     */
    private const PUBLISHABLE_TYPES = [
        'quiz',
        'story',
        'project',
        'ai_chat',
        'coding_challenge',
    ];

    /**
     * Validate type-specific content before a record is published.
     * Incomplete drafts intentionally remain saveable.
     *
     * @param mixed $activityType
     * @param mixed $isPublished
     * @param mixed $content
     */
    public function validateForPublication(
        mixed $activityType,
        mixed $isPublished,
        mixed $content,
    ): void {
        if (! filter_var($isPublished, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $type = strtolower(trim((string) $activityType));

        if (! in_array($type, self::PUBLISHABLE_TYPES, true)) {
            throw ValidationException::withMessages([
                'activity_type' => sprintf(
                    'The %s activity type is not ready for the learner experience yet. Save it as a draft until its learner screen is implemented.',
                    $type !== '' ? $type : 'selected'
                ),
            ]);
        }

        $data = [
            'content' => is_array($content) ? $content : [],
        ];

        $rules = match ($type) {
            'quiz' => [], // Detailed quiz validation is handled by PublishedQuizValidator.
            'story' => [
                'content.story_text' => ['required', 'string', 'min:10', 'max:10000'],
            ],
            'project' => [
                'content.project_description' => ['required', 'string', 'min:10', 'max:10000'],
            ],
            'coding_challenge' => [
                'content.challenge' => ['required', 'string', 'min:10', 'max:10000'],
                'content.difficulty' => ['nullable', 'string', 'in:easy,medium,hard'],
            ],
            'ai_chat' => [
                'content.system_prompt' => ['required', 'string', 'min:10', 'max:3000'],
                'content.goal' => ['required', 'string', 'min:10', 'max:500'],
            ],
            default => [],
        };

        if ($rules === []) {
            return;
        }

        Validator::make($data, $rules, [
            'content.story_text.required' => 'A published story must include story text.',
            'content.story_text.min' => 'A published story must contain at least 10 characters.',
            'content.project_description.required' => 'A published project must include its instructions.',
            'content.project_description.min' => 'Project instructions must contain at least 10 characters.',
            'content.challenge.required' => 'A published coding challenge must include the challenge instructions.',
            'content.challenge.min' => 'Challenge instructions must contain at least 10 characters.',
            'content.system_prompt.required' => 'A published AI activity must include its system prompt.',
            'content.goal.required' => 'A published AI activity must include its learning goal.',
            'content.goal.min' => 'The learning goal must contain at least 10 characters.',
        ])->validate();
    }
}
