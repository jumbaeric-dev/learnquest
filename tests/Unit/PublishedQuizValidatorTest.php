<?php

namespace Tests\Unit;

use App\Services\Learning\PublishedQuizValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublishedQuizValidatorTest extends TestCase
{
    private function validator(): PublishedQuizValidator
    {
        return app(PublishedQuizValidator::class);
    }

    public function test_valid_published_quiz_is_accepted(): void
    {
        $this->validator()->validateForPublication('quiz', true, [
            'question' => 'Which planet is known as the Red Planet?',
            'options' => [
                ['option' => 'Mars'],
                ['option' => 'Venus'],
                ['option' => 'Jupiter'],
            ],
            'answer' => '0',
        ]);

        $this->assertTrue(true);
    }

    public function test_incomplete_quiz_can_be_saved_as_a_draft(): void
    {
        $this->validator()->validateForPublication('quiz', false, [
            'question' => '',
            'options' => [],
            'answer' => null,
        ]);

        $this->assertTrue(true);
    }

    public function test_published_quiz_requires_question(): void
    {
        try {
            $this->validator()->validateForPublication('quiz', true, [
                'question' => '',
                'options' => [
                    ['option' => 'Mars'],
                    ['option' => 'Venus'],
                ],
                'answer' => 0,
            ]);

            $this->fail('Expected the missing quiz question to fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.question', $exception->errors());
        }
    }

    public function test_published_quiz_rejects_answer_index_outside_options(): void
    {
        try {
            $this->validator()->validateForPublication('quiz', true, [
                'question' => 'Which planet is known as the Red Planet?',
                'options' => [
                    ['option' => 'Mars'],
                    ['option' => 'Venus'],
                ],
                'answer' => 2,
            ]);

            $this->fail('Expected an out-of-range answer to fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.answer', $exception->errors());
        }
    }

    public function test_published_quiz_rejects_duplicate_options(): void
    {
        try {
            $this->validator()->validateForPublication('quiz', true, [
                'question' => 'Which planet is known as the Red Planet?',
                'options' => [
                    ['option' => 'Mars'],
                    ['option' => ' mars '],
                ],
                'answer' => 0,
            ]);

            $this->fail('Expected duplicate options to fail validation.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.options.1.option', $exception->errors());
        }
    }

    public function test_non_quiz_activities_are_not_subject_to_quiz_validation(): void
    {
        $this->validator()->validateForPublication('story', true, [
            'story_text' => 'A short story.',
        ]);

        $this->assertTrue(true);
    }
}
