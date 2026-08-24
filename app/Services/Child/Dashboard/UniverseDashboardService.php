<?php

namespace App\Services\Child\Dashboard;

use App\Data\Child\Dashboard\WelcomeDTO;
use App\Data\Child\Dashboard\ExplorerHeaderDTO;
use App\Data\Child\Dashboard\CurrentMissionDTO;
use App\Data\Child\Dashboard\ContinueLearningDTO;
use App\Models\Subject;
use App\Data\Child\Dashboard\SkillPreviewDTO;
use App\Data\Child\Dashboard\LearningWorldDTO;
use App\Data\Child\Dashboard\AchievementDTO;
use App\Data\Child\Dashboard\RecommendedAdventureDTO;
use App\Data\Child\Dashboard\DailyChallengeDTO;
use App\Data\Child\Dashboard\CommunityPreviewDTO;
use App\Data\Child\Dashboard\NovaWidgetDTO;
use App\Data\Child\Dashboard\ExplorerStatsDTO;
use App\Data\Child\Dashboard\UniverseDashboardDTO;
use App\Models\ActivityProgress;
use App\Models\Course;
use App\Models\Child;
use App\Services\Child\Learning\LearningJourneyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Services\FutureReadinessService;
use App\Services\LevelService;

class UniverseDashboardService
{
  public function __construct(
    protected LearningJourneyService $learningJourney,
    protected LevelService $levelService
  ) {
  }

  public function welcome(Child $child): WelcomeDTO
  {
    $mission = $this->learningJourney->getCurrentMission($child);

    return new WelcomeDTO(
      greeting: $this->greeting($child),

      headline: $this->headline($mission),

      message: $this->message($mission),

      buttonText: $this->buttonText($mission),

      buttonAction: "continue"
    );
  }

  protected function greeting(Child $child): string
  {
    $hour = Carbon::now()->hour;

    if ($hour < 12) {
      $greeting = "Good Morning";
    } elseif ($hour < 17) {
      $greeting = "Good Afternoon";
    } elseif ($hour < 22) {
      $greeting = "Good Evening";
    } else {
      $greeting = "Still Exploring";
    }

    return "{$greeting}, {$child->first_name}!";
  }

  protected function headline(?array $mission): string
  {
    if ($mission) {
      return "Ready to continue your mission?";
    }

    return "Ready for your first adventure?";
  }

  protected function message(?array $mission): string
  {
    if (!$mission) {
      return "Choose your first learning adventure and begin earning XP.";
    }

    return sprintf(
      'Continue "%s" and earn %d XP.',
      $mission["activity"]->title,
      $mission["xp"]
    );
  }

  protected function buttonText(?array $mission): string
  {
    return $mission ? "Continue Mission" : "Start Adventure";
  }

  public function explorerHeader(Child $child): ?ExplorerHeaderDTO
  {
    $nextLevelXp = $this->levelService->nextLevelXp($child);

    $percentage = (int) round($this->levelService->progressToNextLevel($child));

    return new ExplorerHeaderDTO(
      name: $child->first_name,

      avatar: $child->avatar,

      level: $child->level,

      xp: $child->xp,

      nextLevelXp: $nextLevelXp,

      xpPercentage: $percentage,

      futureReadiness: (int) round(FutureReadinessService::calculateScore($child)),

      streak: $child->streak?->current_streak ?? 0
    );
  }

  public function currentMission(Child $child): ?CurrentMissionDTO
  {
    $mission = $this->learningJourney->getCurrentMission($child);

    if (!$mission) {
      return null;
    }

    return new CurrentMissionDTO(
      course: $mission["course"]->title,

      lesson: $mission["lesson"]->title,

      activity: $mission["activity"]->title,

      progress: $mission["progress"],

      xpReward: $mission["xp"],

      estimatedMinutes: $mission["minutes"],

      buttonText: "Continue",

      buttonAction: "continue"
    );
  }

  public function continueLearning(Child $child): ?ContinueLearningDTO
  {
    $course = $this->learningJourney->getCurrentCourse($child);

    $lesson = $this->learningJourney->getCurrentLesson($child);

    if (!$course || !$lesson) {
      return null;
    }

    return new ContinueLearningDTO(
      course: $course->title,

      lesson: $lesson->title,

      progress: $this->learningJourney->getCourseProgressPercentage(
        $child,
        $course
      ),

      buttonText: "Continue",

      buttonAction: "continue"
    );
  }

  public function learningWorlds(): array
  {
    return Subject::query()

      ->where("is_active", true)

      ->withCount([
        "courses" => fn($query) => $query->where("is_published", true),
      ])

      ->orderBy("name")

      ->get()

      ->map(
        fn(Subject $subject) => new LearningWorldDTO(
          id: $subject->id,

          name: $subject->name,

          icon: $subject->icon,

          courses: $subject->courses_count
        )
      )

      ->all();
  }

  public function skills(Child $child): array
  {
    return $child

      ->skillProgress()

      ->with("skill")

      ->orderByDesc("xp")

      ->take(3)

      ->get()

      ->map(function ($progress) {
        return new SkillPreviewDTO(
          name: $progress->skill->name,

          icon: $progress->skill->icon,

          xp: $progress->xp,

          progress: (int) round($progress->progress_percentage)
        );
      })

      ->all();
  }

  public function achievements(Child $child): array
  {
    return $child

      ->badges()

      ->latest("pivot_earned_at")

      ->take(5)

      ->get()

      ->map(function ($badge) {
        return new AchievementDTO(
          name: $badge->name,

          description: $badge->description,

          icon: $badge->icon,

          earnedAt: optional($badge->pivot->earned_at)?->format("M d, Y")
        );
      })

      ->all();
  }

  public function recommendedAdventures(Child $child): array
  {
    $currentCourse = $this->learningJourney->getCurrentCourse($child);

    return Course::query()

      ->where("is_published", true)

      ->when(
        $currentCourse,
        fn($query) => $query->where("id", "!=", $currentCourse->id)
      )

      ->with("subject")

      ->limit(5)

      ->get()

      ->map(function (Course $course) {
        return new RecommendedAdventureDTO(
          id: $course->id,

          title: $course->title,

          description: $course->description,

          icon: $course->subject?->icon,

          ageGroup: $course->age_group
        );
      })

      ->all();
  }

  public function dailyChallenge(Child $child): DailyChallengeDTO
  {
    $completedToday = ActivityProgress::query()

      ->where("child_id", $child->id)

      ->where("completed", true)

      ->whereDate("completed_at", today())

      ->count();

    return new DailyChallengeDTO(
      title: "Daily Explorer",

      description: "Complete 3 learning activities today",

      current: $completedToday,

      target: 3,

      xpReward: 30,

      buttonText: $completedToday >= 3 ? "Completed 🎉" : "Start Challenge"
    );
  }

  public function communityPreview(): CommunityPreviewDTO
  {
    return new CommunityPreviewDTO(
      title: "Explore Together",

      message: "Learning is more fun when adventures are shared.",

      features: [
        [
          "icon" => "heroicon-o-user-group",
          "text" => "Meet other explorers",
        ],

        [
          "icon" => "heroicon-o-trophy",
          "text" => "Celebrate achievements",
        ],

        [
          "icon" => "heroicon-o-rocket-launch",
          "text" => "Join team missions",
        ],
      ],

      buttonText: "Coming Soon"
    );
  }

  public function novaWidget(Child $child): NovaWidgetDTO
  {
    return new NovaWidgetDTO(
      greeting: "Hi {$child->first_name}! 👋",

      message: "I'm Nova. Ready for your next adventure?",

      buttonText: "Ask Nova",

      action: "open-nova",

      icon: "heroicon-o-sparkles"
    );
  }

  public function explorerStats(Child $child): ExplorerStatsDTO
  {
    return new ExplorerStatsDTO(
      level: $child->level,

      xp: $child->xp,

      streak: $child->streak?->current_streak ?? 0
    );
  }

  public function dashboard(Child $child): UniverseDashboardDTO
  {
    return new UniverseDashboardDTO(
      explorer: $this->explorerHeader($child),
      welcome: $this->welcome($child),
      nova: $this->novaWidget($child),
      mission: $this->currentMission($child),
      learning: $this->continueLearning($child),
      skills: $this->skills($child),
      achievements: $this->achievements($child),
      worlds: $this->learningWorlds(),
      recommendations: $this->recommendedAdventures($child),
      dailyChallenge: $this->dailyChallenge($child),
      community: $this->communityPreview()
    );
  }

  // public function cachedDashboard(
  //     Child $child
  // ) {

  //     return Cache::remember(

  //         "child-dashboard-{$child->id}",

  //         now()->addMinutes(5),

  //         fn() =>
  //         $this->dashboard($child)

  //     );
  // }

  public function cachedDashboard(Child $child): UniverseDashboardDTO
  {
    return $this->dashboard($child);
  }
}
