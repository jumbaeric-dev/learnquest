<x-child.layout.app-shell
    :theme="$theme"
    :explorer="$explorer"
    active="courses"
>

    @php
        $course = $lesson->module->course;
    @endphp

    <x-child.layout.section-stack spacing="relaxed">

        {{-- Back link --}}
        <div>
            <x-lq.button
                tag="a"
                href="{{ route('learn.course', $course) }}"
                variant="secondary"
                size="sm"
            >
                ← Back to Course
            </x-lq.button>
        </div>


        {{-- Lesson hero --}}
        <x-lq.glass-card
            variant="hero"
            padding="lg"
            class="lq-course-hero"
        >
            <div class="lq-course-hero__inner">

                <div class="lq-course-hero__main">

                    <div class="lq-course-hero__icon" aria-hidden="true">
                        <x-lq.world-icon
                            :name="$course->subject?->icon"
                            fallback="📘"
                            class="h-10 w-10"
                        />
                    </div>

                    <div class="lq-course-hero__body">

                        <div class="lq-course-hero__tags">

                            <span class="lq-course-hero__tag">
                                📚 {{ $course->title }}
                            </span>

                            @if ($lesson->estimated_minutes)
                                <span class="lq-course-hero__tag">
                                    ⏱ {{ $lesson->estimated_minutes }} min
                                </span>
                            @endif

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $lesson->title }}
                        </h1>

                        @if ($lesson->description)
                            <p class="lq-course-hero__description">
                                {{ $lesson->description }}
                            </p>
                        @endif

                    </div>

                </div>


                {{-- Progress panel --}}
                <div class="lq-course-hero__progress">

                    <div class="lq-course-hero__progress-head">
                        <span class="lq-course-hero__progress-label">
                            Your Progress
                        </span>

                        <strong class="lq-course-hero__progress-value">
                            {{ $this->progressPercentage }}%
                        </strong>
                    </div>

                    <div class="lq-course-hero__track">
                        <div
                            class="lq-course-hero__fill"
                            style="width: {{ $this->progressPercentage }}%"
                        ></div>
                    </div>

                    <p class="lq-course-hero__meta">
                        @if ($this->isCompleted)
                            🎉 Lesson completed — amazing work!
                        @else
                            {{ $this->completedActivities }} of {{ $this->totalActivities }} activities completed
                        @endif
                    </p>

                </div>

            </div>

        </x-lq.glass-card>


        {{-- Quick stats --}}
        <div class="grid grid-cols-3 gap-4">

            <x-lq.stat-chip
                icon="🎯"
                :value="$this->totalActivities"
                label="Activities"
                variant="primary"
            />

            <x-lq.stat-chip
                icon="✅"
                :value="$this->completedActivities"
                label="Completed"
                variant="success"
            />

            <x-lq.stat-chip
                icon="⭐"
                :value="$lesson->xp_reward ?? 0"
                label="Lesson XP"
                variant="reward"
            />

        </div>


        {{-- Activities --}}
        <x-lq.section-title subtitle="Finish every activity to complete this lesson.">
            🧩 Activities
        </x-lq.section-title>

        @php
            $nextActivityFound = false;
        @endphp

        @if ($lesson->activities->isNotEmpty())

            <x-lq.glass-card padding="lg">

                <div class="space-y-3">

                    @foreach ($lesson->activities as $index => $activity)

                        @php
                            $completed = $this->isActivityCompleted($activity->id);

                            $isNext = ! $this->isCompleted && ! $completed && ! $nextActivityFound;

                            if ($isNext) {
                                $nextActivityFound = true;
                            }

                            [$typeIcon, $typeLabel] = match ($activity->activity_type) {
                                'quiz' => ['❓', 'Quiz'],
                                'story' => ['📖', 'Story'],
                                'project' => ['🛠️', 'Project'],
                                'ai_chat' => ['🤖', 'Chat with AI'],
                                'coding_challenge' => ['💻', 'Coding Challenge'],
                                'video' => ['🎬', 'Video'],
                                default => ['🎯', ucfirst(str_replace('_', ' ', (string) $activity->activity_type))],
                            };
                        @endphp

                        <article
                            wire:key="activity-{{ $activity->id }}"
                            @class([
                                'flex flex-col gap-4 rounded-2xl border p-4 transition hover:shadow-md sm:flex-row sm:items-center',
                                'border-emerald-200 bg-emerald-50/60' => $completed,
                                'bg-[color:var(--lq-theme-soft)]' => $isNext,
                                'border-slate-200 bg-white' => ! $completed && ! $isNext,
                            ])
                            @style([
                                'border-color: color-mix(in srgb, var(--lq-theme-primary) 35%, transparent)' => $isNext,
                            ])
                        >

                            <div
                                @class([
                                    'flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-2xl',
                                    'bg-emerald-500 text-white' => $completed,
                                    'bg-white' => ! $completed,
                                ])
                            >
                                @if ($completed)
                                    ✓
                                @else
                                    {{ $typeIcon }}
                                @endif
                            </div>


                            <div class="min-w-0 flex-1 space-y-1">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h3 class="font-semibold text-slate-800">
                                        {{ $activity->title }}
                                    </h3>

                                    @if ($isNext)
                                        <x-lq.badge variant="primary" size="sm">
                                            Up Next
                                        </x-lq.badge>
                                    @endif

                                </div>

                                <p class="text-xs font-medium text-slate-400">
                                    {{ $typeIcon }} {{ $typeLabel }}
                                    ·
                                    ⭐ {{ $activity->xp_reward }} XP
                                </p>

                            </div>


                            <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">

                                @if ($completed)
                                    <x-lq.badge variant="success">
                                        ✓ Completed
                                    </x-lq.badge>

                                    <x-lq.button
                                        tag="a"
                                        href="{{ route('learn.activity', $activity) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Review
                                    </x-lq.button>

                                @elseif ($isNext)
                                    <x-lq.button
                                        tag="a"
                                        href="{{ route('learn.activity', $activity) }}"
                                        variant="primary"
                                        size="sm"
                                    >
                                        ▶ Start
                                    </x-lq.button>

                                @else
                                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">
                                        🔒 Complete the previous activity first
                                    </span>
                                @endif

                            </div>

                        </article>

                    @endforeach

                </div>

            </x-lq.glass-card>

        @else

            <x-lq.empty-state
                icon="🚀"
                title="Activities are on the way"
                message="No activities are available for this lesson yet — check back soon!"
                action="Back to Course"
                href="{{ route('learn.course', $course) }}"
            />

        @endif


        {{-- Finished: send them onward --}}
        @if ($this->isCompleted)

            <x-lq.glass-card padding="lg">

                <div class="mx-auto max-w-md space-y-4 text-center">

                    <div class="text-5xl">🎉</div>

                    <h2 class="text-2xl font-extrabold text-slate-800">
                        Lesson Complete!
                    </h2>

                    <p class="text-slate-600">
                        You finished every activity. Ready for what's next?
                    </p>

                    <x-lq.button
                        tag="a"
                        href="{{ route('learn.course', $course) }}"
                        variant="primary"
                    >
                        🗺️ Back to Adventure Map
                    </x-lq.button>

                </div>

            </x-lq.glass-card>

        @endif

    </x-child.layout.section-stack>

</x-child.layout.app-shell>