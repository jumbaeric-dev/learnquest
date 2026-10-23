<?php

namespace App\Services\Learning;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidatorInstance;

class PublishedQuizValidator
{
    /**
     * Ensure quizzes contain usable questions and answers before publishing.
     * Drafts and non-quiz activities may remain incomplete while being authored.
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
        if (
            strtolower((string) $activityType) !== 'quiz'
            || ! filter_var($isPublished, FILTER_VALIDATE_BOOLEAN)
        ) {
            return;
        }

        $data = [
            'content' => is_array($content) ? $content : [],
        ];

        $validator = Validator::make($data, [
            'content' => ['required', 'array'],
            'content.question' => ['required', 'string', 'max:1000'],
            'content.options' => ['required', 'array', 'min:2', 'max:6'],
            'content.options.*.option' => ['required', 'string', 'max:255'],
            'content.answer' => ['required', 'integer', 'min:0'],
        ], [
            'content.question.required' => 'A published quiz must have a question.',
            'content.options.required' => 'Add at least two answer options before publishing.',
            'content.options.min' => 'A published quiz must have at least two answer options.',
            'content.options.max' => 'A quiz cannot have more than six answer options.',
            'content.options.*.option.required' => 'Every answer option must have text.',
            'content.answer.required' => 'Choose the correct answer before publishing.',
            'content.answer.integer' => 'The correct answer must reference one of the listed options.',
            'content.answer.min' => 'Choose a valid correct answer.',
        ]);

        $validator->after(function (ValidatorInstance $validator) use ($data): void {
            $content = $data['content'];
            $options = $content['options'] ?? null;

            if (! is_array($options)) {
                return;
            }

            $normalizedLabels = [];

            foreach (array_values($options) as $index => $option) {
                if (! is_array($option)) {
                    continue;
                }

                $label = $option['option'] ?? null;

                if (! is_string($label) || trim($label) === '') {
                    continue;
                }

                $normalized = mb_strtolower(trim($label));

                if (isset($normalizedLabels[$normalized])) {
                    $validator->errors()->add(
                        "content.options.{$index}.option",
                        'Answer options must be distinct.'
                    );

                    continue;
                }

                $normalizedLabels[$normalized] = true;
            }

            $rawAnswer = $content['answer'] ?? null;

            if (is_int($rawAnswer)) {
                $answer = $rawAnswer;
            } elseif (
                is_string($rawAnswer)
                && preg_match('/^(0|[1-9][0-9]*)$/D', $rawAnswer) === 1
            ) {
                $answer = (int) $rawAnswer;
            } else {
                return;
            }

            if ($answer >= count($options)) {
                $validator->errors()->add(
                    'content.answer',
                    'The correct answer must match one of the listed options.'
                );
            }
        });

        $validator->validate();
    }
}
