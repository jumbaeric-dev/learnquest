<?php

namespace Tests\Unit;

use App\Services\Learning\PublishedActivityContentValidator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PublishedActivityContentValidatorTest extends TestCase
{
    private function validator(): PublishedActivityContentValidator
    {
        return app(PublishedActivityContentValidator::class);
    }

    public function test_incomplete_unsupported_activity_can_be_saved_as_draft(): void
    {
        $this->validator()->validateForPublication('flashcard', false, []);

        $this->assertTrue(true);
    }

    public function test_unsupported_activity_type_cannot_be_published(): void
    {
        try {
            $this->validator()->validateForPublication('flashcard', true, [
                'front' => 'Question',
                'back' => 'Answer',
            ]);

            $this->fail('Expected an unsupported learner activity type to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('activity_type', $exception->errors());
        }
    }

    public function test_published_story_requires_non_empty_story_text(): void
    {
        try {
            $this->validator()->validateForPublication('story', true, [
                'story_text' => '   ',
            ]);

            $this->fail('Expected story content to be required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.story_text', $exception->errors());
        }
    }

    public function test_valid_published_story_is_accepted(): void
    {
        $this->validator()->validateForPublication('story', true, [
            'story_text' => 'A young explorer discovers a new planet.',
        ]);

        $this->assertTrue(true);
    }

    public function test_published_project_requires_instructions(): void
    {
        try {
            $this->validator()->validateForPublication('project', true, []);

            $this->fail('Expected project instructions to be required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.project_description', $exception->errors());
        }
    }

    public function test_published_coding_challenge_requires_challenge_text(): void
    {
        try {
            $this->validator()->validateForPublication('coding_challenge', true, [
                'difficulty' => 'easy',
            ]);

            $this->fail('Expected coding instructions to be required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.challenge', $exception->errors());
        }
    }

    public function test_published_ai_guided_activity_requires_prompt_and_goal(): void
    {
        try {
            $this->validator()->validateForPublication('ai_chat', true, [
                'system_prompt' => 'You are a friendly guide for children.',
            ]);

            $this->fail('Expected the learning goal to be required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('content.goal', $exception->errors());
        }
    }

    public function test_non_publish_transition_does_not_reject_incomplete_content(): void
    {
        $this->validator()->validateForPublication('project', false, [
            'project_description' => '',
        ]);

        $this->assertTrue(true);
    }
}
