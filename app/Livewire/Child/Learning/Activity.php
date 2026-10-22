<?php

namespace App\Livewire\Child\Learning;

use App\Livewire\Child\Learning\Concerns\InteractsWithLearningShell;
use App\Models\ActivityProgress;
use App\Models\LessonActivity as LessonActivityModel;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Progress\LearningEngineService;
use Livewire\Component;

class Activity extends Component
{
    use InteractsWithLearningShell;

    public LessonActivityModel $activity;

    public ?ActivityProgress $progress = null;

    public bool $submitted = false;

    public ?int $selectedOption = null;

    public ?int $score = null;

    public ?array $result = null;

    public function mount(
        LessonActivityModel $activity,
        LearningContentAccessService $access,
        CurrentChildService $currentChildService,
    ): void {
        $child = $currentChildService->current();

        abort_unless($child, 403);

        $this->activity = $access->activity($activity, $child);

        $this->activity->loadMissing('lesson.module.course.subject');

        $this->prepareShell(
            $child,
            $this->activity->lesson->module->course->subject?->slug
        );

        $this->progress = $child
            ->activityProgress()
            ->where('activity_id', $this->activity->id)
            ->first();

        if ($this->progress?->completed) {
            $this->submitted = true;
            $this->score = $this->progress->score;
        }
    }

    /**
     * Submit a quiz option.
     *
     * The correct answer is evaluated server-side. Access is rechecked
     * on each action because Livewire actions are client-triggered.
     */
    public function submitOption(
        int $option,
        CurrentChildService $currentChildService,
        LearningEngineService $learningEngine,
        LearningContentAccessService $access,
    ): void {
        $child = $currentChildService->current();

        abort_unless($child, 403);

        $this->activity = $access->activity($this->activity, $child);
        $this->progress = $child
            ->activityProgress()
            ->where('activity_id', $this->activity->id)
            ->first();

        if ($this->progress?->completed) {
            $this->submitted = true;
            $this->score = $this->progress->score;

            return;
        }

        $content = $this->activity->content;

        abort_unless(
            is_array($content) && ! empty($content['question']),
            422,
            'This activity does not contain a valid quiz question.'
        );

        $options = $content['options'] ?? null;

        abort_unless(
            is_array($options)
                && array_is_list($options)
                && $options !== []
                && array_key_exists($option, $options),
            422,
            'This quiz does not contain valid answer options.'
        );

        // Never coerce a missing or malformed answer to index 0.
        // Without this validation, (int) null would make the first
        // option look correct for a malformed quiz.
        $rawCorrectAnswer = $content['answer'] ?? null;

        if (is_int($rawCorrectAnswer)) {
            $correctAnswer = $rawCorrectAnswer;
        } elseif (
            is_string($rawCorrectAnswer)
            && preg_match('/^(0|[1-9][0-9]*)$/D', $rawCorrectAnswer) === 1
        ) {
            $correctAnswer = (int) $rawCorrectAnswer;
        } else {
            abort(
                422,
                'This quiz has an invalid answer configuration.'
            );
        }

        abort_unless(
            array_key_exists($correctAnswer, $options),
            422,
            'This quiz answer does not match an available option.'
        );

        $score = $option === $correctAnswer ? 100 : 0;

        $this->selectedOption = $option;
        $this->score = $score;

        $this->result = $learningEngine->completeActivity(
            $child,
            $this->activity,
            $score
        );

        $this->progress = $this->result['activity_progress'];
        $this->submitted = true;
    }

    /**
     * Complete a non-quiz activity.
     *
     * Access is rechecked here too so direct Livewire calls cannot
     * bypass the lesson/activity progression boundary.
     */
    public function complete(
        CurrentChildService $currentChildService,
        LearningEngineService $learningEngine,
        LearningContentAccessService $access,
    ): void {
        $child = $currentChildService->current();

        abort_unless($child, 403);

        $this->activity = $access->activity($this->activity, $child);
        $this->progress = $child
            ->activityProgress()
            ->where('activity_id', $this->activity->id)
            ->first();

        if ($this->progress?->completed) {
            $this->submitted = true;
            $this->score = $this->progress->score;

            return;
        }

        // Quiz completion must go through submitOption(), which
        // evaluates the selected answer on the server. Never allow
        // a direct Livewire call to complete a quiz for 100 points.
        abort_unless(
            ! $this->isQuizActivity(),
            403,
            'Quiz activities must be completed by submitting an answer.'
        );

        $this->score = 100;

        $this->result = $learningEngine->completeActivity(
            $child,
            $this->activity,
            100
        );

        $this->progress = $this->result['activity_progress'];
        $this->submitted = true;
    }

    /**
     * Let the child try again after a failed attempt.
     */
    public function retry(): void
    {
        if ($this->progress?->completed) {
            return;
        }

        $this->submitted = false;
        $this->selectedOption = null;
        $this->score = null;
        $this->result = null;
    }

    /**
     * Keep this aligned with the view, which renders quiz UI when
     * question content is present even if the activity type is wrong.
     */
    private function isQuizActivity(): bool
    {
        $content = $this->activity->content;

        return strtolower((string) $this->activity->activity_type) === 'quiz'
            || (is_array($content) && ! empty($content['question']));
    }

    public function render()
    {
        return view('livewire.child.learning.activity');
    }
}
