<?php

namespace App\Services\ParentPortal;

use App\Models\ActivityProgress;
use App\Models\ChildStreak;
use App\Models\CourseProgress;
use App\Models\LessonProgress;
use App\Models\Child;
use App\Models\NovaMessage;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ParentDashboardService
{
  public const WINDOW_DAYS = 7;

  public const COURSES_PER_CHILD = 3;

  /**
   * Build the family overview for one parent.
   *
   * Ownership is structural: children are only ever loaded through
   * $parent->children(), so another family's data can never be returned.
   */
  public function overview(User $parent): array
  {
    $children = $parent
      ->children()
      ->with("streak")
      ->orderBy("first_name")
      ->get();

    if ($children->isEmpty()) {
      return $this->payload([]);
    }

    $ids = $children->modelKeys();
    $tz = $this->timezone();
    $now = Carbon::now($tz);

    /*
     * Query bindings are formatted without converting timezones, so the
     * window start is converted to the application timezone first.
     */
    $since = $now
      ->copy()
      ->subDays(self::WINDOW_DAYS - 1)
      ->startOfDay()
      ->setTimezone(config("app.timezone"));

    $activity = ActivityProgress::query()
      ->whereIn("child_id", $ids)
      ->where("completed", true)
      ->where("completed_at", ">=", $since)
      ->get(["child_id", "completed_at", "xp_earned"])
      ->groupBy("child_id");

    $lessons = LessonProgress::query()
      ->whereIn("child_id", $ids)
      ->where("completed", true)
      ->where("completed_at", ">=", $since)
      ->selectRaw("child_id, COUNT(*) as total")
      ->groupBy("child_id")
      ->pluck("total", "child_id");

    $badges = DB::table("badge_child")
      ->whereIn("child_id", $ids)
      ->where("earned_at", ">=", $since)
      ->selectRaw("child_id, COUNT(*) as total")
      ->groupBy("child_id")
      ->pluck("total", "child_id");

    $lastActive = ActivityProgress::query()
      ->whereIn("child_id", $ids)
      ->where("completed", true)
      ->selectRaw("child_id, MAX(completed_at) as last_at")
      ->groupBy("child_id")
      ->pluck("last_at", "child_id");

    $courses = CourseProgress::query()
      ->whereIn("child_id", $ids)
      ->where("completed", false)
      ->where("progress_percentage", ">", 0)
      ->with("course.subject")
      ->orderByDesc("updated_at")
      ->get()
      ->groupBy("child_id");

    $summaries = $children
      ->map(function ($child) use (
        $activity,
        $lessons,
        $badges,
        $lastActive,
        $courses,
        $tz,
        $now
      ) {
        $rows = $activity->get($child->id, collect());

        $activeDays = $rows
          ->map(
            fn($row) => $row->completed_at
              ->copy()
              ->setTimezone($tz)
              ->toDateString()
          )
          ->unique()
          ->count();

        return [
          "id" => $child->id,
          "name" => $child->full_name,
          "first_name" => $child->first_name,
          "level" => (int) $child->level,
          "xp" => (int) $child->xp,
          "is_active" => (bool) $child->is_active,
          "streak" => [
            "current" => $this->currentStreak($child->streak),
            "longest" => (int) ($child->streak?->longest_streak ?? 0),
          ],
          "last_active_label" => $this->lastActiveLabel(
            $lastActive->get($child->id),
            $now
          ),
          "week" => [
            "activities" => $rows->count(),
            "xp" => (int) $rows->sum("xp_earned"),
            "active_days" => $activeDays,
            "lessons" => (int) ($lessons[$child->id] ?? 0),
            "badges" => (int) ($badges[$child->id] ?? 0),
          ],
          "courses" => $courses
            ->get($child->id, collect())
            ->filter(fn($progress) => $progress->course !== null)
            ->take(self::COURSES_PER_CHILD)
            ->map(
              fn($progress) => [
                "title" => $progress->course->title,
                "subject" => $progress->course->subject?->name,
                "percentage" => (float) $progress->progress_percentage,
              ]
            )
            ->values()
            ->all(),
        ];
      })
      ->values()
      ->all();

    $familyActiveDays = $activity
      ->flatten(1)
      ->map(fn($row) => $row->completed_at
        ->copy()
        ->setTimezone($tz)
        ->toDateString())
      ->unique()
      ->count();

    return $this->payload($summaries, $familyActiveDays);
  }

  /**
   * Build the detailed learning report for one child owned by the parent.
   * Ownership is enforced here as well as at the HTTP authorization boundary.
   */
  public function childDetails(User $parent, int $childId): array
  {
    $child = $parent->children()
      ->with(['streak', 'badges'])
      ->findOrFail($childId);

    $tz = $this->timezone();
    $now = Carbon::now($tz);
    $since = $now->copy()->subDays(13)->startOfDay()
      ->setTimezone(config('app.timezone'));
    $weekSince = $now->copy()->subDays(6)->startOfDay()
      ->setTimezone(config('app.timezone'));

    $courseProgress = CourseProgress::query()
      ->where('child_id', $child->id)
      ->with('course.subject')
      ->orderByDesc('updated_at')
      ->get()
      ->filter(fn($progress) => $progress->course !== null)
      ->map(fn($progress) => [
        'title' => $progress->course->title,
        'subject' => $progress->course->subject?->name,
        'percentage' => (float) $progress->progress_percentage,
        'completed' => (bool) $progress->completed,
        'updated_at' => $progress->updated_at?->setTimezone($tz)->format('M j, Y'),
      ])->values()->all();

    $skills = $child->skillProgress()
      ->with('skill')
      ->get()
      ->filter(fn($progress) => $progress->skill !== null)
      ->map(fn($progress) => [
        'name' => $progress->skill->name,
        'progress' => (float) $progress->progress_percentage,
        'xp' => (int) $progress->xp,
        'level' => (int) $progress->level,
      ])->values();

    $recentActivity = ActivityProgress::query()
      ->where('child_id', $child->id)
      ->where('completed', true)
      ->with('activity.lesson.module.course')
      ->orderByDesc('completed_at')
      ->limit(12)
      ->get()
      ->map(fn($progress) => [
        'title' => $progress->activity?->title ?? 'Learning activity',
        'course' => $progress->activity?->lesson?->module?->course?->title,
        'score' => $progress->score === null ? null : (int) $progress->score,
        'xp' => (int) $progress->xp_earned,
        'completed_at' => $progress->completed_at?->setTimezone($tz)->format('M j, Y · g:i A'),
      ])->all();

    $badges = $child->badges()
      ->orderByPivot('earned_at', 'desc')
      ->limit(12)
      ->get()
      ->map(fn($badge) => [
        'name' => $badge->name,
        'description' => $badge->description,
        'icon' => $badge->icon,
        'earned_at' => $badge->pivot->earned_at
          ? Carbon::parse($badge->pivot->earned_at)->setTimezone($tz)->format('M j, Y')
          : null,
      ])->all();

    $activityDates = ActivityProgress::query()
      ->where('child_id', $child->id)
      ->where('completed', true)
      ->where('completed_at', '>=', $since)
      ->get(['completed_at'])
      ->map(fn($row) => $row->completed_at?->copy()->setTimezone($tz)->toDateString())
      ->filter()->unique()->sort()->values()->all();

    $dailyChallenges = DB::table('daily_challenge_claims')
      ->where('child_id', $child->id)
      ->where('challenge_date', '>=', $now->copy()->subDays(13)->toDateString())
      ->orderByDesc('challenge_date')
      ->get(['challenge_key', 'challenge_date', 'xp_awarded'])
      ->map(fn($claim) => [
        'name' => str($claim->challenge_key)->replace(['_', '-'], ' ')->title()->toString(),
        'date' => Carbon::parse($claim->challenge_date)->format('M j, Y'),
        'xp' => (int) $claim->xp_awarded,
      ])->all();

    // Count only child-authored prompts. Never return message content.
    $novaMessagesThisWeek = NovaMessage::query()
      ->where('child_id', $child->id)
      ->where('role', 'user')
      ->where('created_at', '>=', $weekSince)
      ->count();

    return [
      'child' => [
        'id' => $child->id,
        'name' => $child->full_name,
        'username' => $child->username,
        'avatar' => $child->avatar,
        'level' => (int) $child->level,
        'xp' => (int) $child->xp,
        'is_active' => (bool) $child->is_active,
      ],
      'streak' => [
        'current' => $this->currentStreak($child->streak),
        'longest' => (int) ($child->streak?->longest_streak ?? 0),
        'last_activity_date' => $child->streak?->last_activity_date?->format('M j, Y'),
        'active_dates' => $activityDates,
      ],
      'courses' => $courseProgress,
      'skills' => [
        'strongest' => $skills->sortByDesc('progress')->take(5)->values()->all(),
        'growth_areas' => $skills->sortBy('progress')->take(5)->values()->all(),
      ],
      'recent_activity' => $recentActivity,
      'badges' => $badges,
      'daily_challenges' => $dailyChallenges,
      'nova' => [
        'messages_this_week' => $novaMessagesThisWeek,
      ],
    ];
  }

  protected function payload(array $children, int $familyActiveDays = 0): array
  {
    $collection = collect($children);

    return [
      "window_days" => self::WINDOW_DAYS,
      "family" => [
        "children" => $collection->count(),
        "active_children" => $collection
          ->filter(fn($child) => $child["week"]["activities"] > 0)
          ->count(),
        "activities" => (int) $collection->sum("week.activities"),
        "lessons" => (int) $collection->sum("week.lessons"),
        "xp" => (int) $collection->sum("week.xp"),
        "active_days" => $familyActiveDays,
        "badges" => (int) $collection->sum("week.badges"),
      ],
      "children" => $children,
    ];
  }

  /**
   * A stored streak is only still "alive" if the last activity was today
   * or yesterday. StreakService records days in the application timezone,
   * so the check uses the same.
   */
  protected function currentStreak(?ChildStreak $streak): int
  {
    if (!$streak?->last_activity_date) {
      return 0;
    }

    $last = $streak->last_activity_date->toDateString();
    $today = Carbon::now($this->timezone())->startOfDay();

    if (
      $last === $today->toDateString() ||
      $last === $today->copy()->subDay()->toDateString()
    ) {
      return (int) $streak->current_streak;
    }

    return 0;
  }

  protected function lastActiveLabel(?string $raw, Carbon $now): string
  {
    if (!$raw) {
      return "No activity yet";
    }

    $at = Carbon::parse($raw, config("app.timezone"))->setTimezone(
      $this->timezone()
    );

    $days = (int) $at
      ->copy()
      ->startOfDay()
      ->diffInDays($now->copy()->startOfDay(), true);

    return match (true) {
      $days === 0 => "Today",
      $days === 1 => "Yesterday",
      default => "{$days} days ago",
    };
  }

  protected function timezone(): string
  {
    return config("learnquest.daily_challenges.timezone") ?:
      config("app.timezone");
  }
}
