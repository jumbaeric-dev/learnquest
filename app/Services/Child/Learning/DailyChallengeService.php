<?php

namespace App\Services\Child\Learning;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\DailyChallengeClaim;
use App\Models\LessonProgress;
use App\Models\NovaMessage;
use App\Services\BadgeService;
use App\Services\LevelService;
use App\Services\XPService;
use Carbon\CarbonInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyChallengeService
{
    public function __construct(
        protected XPService $xpService,
        protected LevelService $levelService,
        protected BadgeService $badgeService,
    ) {}

    /**
     * Today's challenges for a child, with real completion/claim state.
     *
     * @return array<int, array{
     *     key: string,
     *     icon: string,
     *     title: string,
     *     description: string,
     *     xp: int,
     *     completed: bool,
     *     claimed: bool
     * }>
     */
    public function forChild(Child $child): array
    {
        [$start, $end, $date] = $this->today();

        $claimed = DailyChallengeClaim::query()
            ->where('child_id', $child->id)
            ->where('challenge_date', $date)
            ->pluck('challenge_key')
            ->all();

        $challenges = [];

        foreach ($this->definitions() as $key => $definition) {
            $challenges[] = [
                'key' => $key,
                'icon' => $definition['icon'],
                'title' => $definition['title'],
                'description' => $definition['description'] ?? '',
                'xp' => (int) $definition['xp'],
                'completed' => $this->isCompleted($child, $key, $start, $end),
                'claimed' => in_array($key, $claimed, true),
            ];
        }

        return $challenges;
    }

    /**
     * Claim a completed challenge's XP, once per day.
     *
     * Returns null when the challenge is unknown, not completed yet,
     * or already claimed today.
     *
     * @return array{awarded: int, xp: int, leveled_up: bool}|null
     */
    public function claim(Child $child, string $key): ?array
    {
        $definition = $this->definitions()[$key] ?? null;

        if (! $definition) {
            return null;
        }

        [$start, $end, $date] = $this->today();

        try {
            return DB::transaction(function () use ($child, $key, $definition, $start, $end, $date) {
                // Serialize competing claims for the same child.
                $child = Child::query()
                    ->whereKey($child->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $this->isCompleted($child, $key, $start, $end)) {
                    return null;
                }

                $alreadyClaimed = DailyChallengeClaim::query()
                    ->where('child_id', $child->id)
                    ->where('challenge_key', $key)
                    ->where('challenge_date', $date)
                    ->exists();

                if ($alreadyClaimed) {
                    return null;
                }

                $xp = (int) $definition['xp'];

                DailyChallengeClaim::create([
                    'child_id' => $child->id,
                    'challenge_key' => $key,
                    'challenge_date' => $date,
                    'xp_awarded' => $xp,
                ]);

                $this->xpService->award($child, $xp);

                $level = $this->levelService->update($child);

                $this->badgeService->evaluate($child);

                return [
                    'awarded' => $xp,
                    'xp' => $child->fresh()->xp,
                    'leveled_up' => $level['leveled_up'],
                ];
            });
        } catch (UniqueConstraintViolationException) {
            // A concurrent request claimed it first.
            return null;
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function definitions(): array
    {
        return config('learnquest.daily_challenges.challenges', []);
    }

    /**
     * Today's window in the configured timezone.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface, 2: string}
     *         [start, end] converted to the app timezone (how timestamps
     *         are stored), plus the local calendar date (Y-m-d).
     */
    protected function today(): array
    {
        $timezone = config(
            'learnquest.daily_challenges.timezone',
            config('app.timezone')
        );

        $startLocal = Carbon::now($timezone)->startOfDay();

        $start = $startLocal->copy()->setTimezone(config('app.timezone'));

        $end = $startLocal->copy()
            ->addDay()
            ->setTimezone(config('app.timezone'));

        return [$start, $end, $startLocal->toDateString()];
    }

    protected function isCompleted(
        Child $child,
        string $key,
        CarbonInterface $start,
        CarbonInterface $end
    ): bool {
        return match ($key) {

            'complete_lesson' => LessonProgress::query()
                ->where('child_id', $child->id)
                ->where('completed', true)
                ->where('completed_at', '>=', $start)
                ->where('completed_at', '<', $end)
                ->exists(),

            'create_something' => ActivityProgress::query()
                ->where('child_id', $child->id)
                ->where('completed', true)
                ->where('completed_at', '>=', $start)
                ->where('completed_at', '<', $end)
                ->whereHas(
                    'activity',
                    fn ($query) => $query->where('activity_type', 'project')
                )
                ->exists(),

            'ask_nova' => NovaMessage::query()
                ->where('child_id', $child->id)
                ->where('role', 'user')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<', $end)
                ->exists(),

            default => false,
        };
    }
}