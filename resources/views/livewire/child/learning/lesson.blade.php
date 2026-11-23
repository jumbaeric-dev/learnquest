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
                ← {{ __('localization.back_to_course') }}
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
                                📚 {{ $course->localized('title') }}
                            </span>

                            @if ($lesson->estimated_minutes)
                                <span class="lq-course-hero__tag">
                                    ⏱ {{ $lesson->estimated_minutes }} min
                                </span>
                            @endif

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $lesson->localized('title') }}
                        </h1>

                        @if ($lesson->localized('description'))
                            <p class="lq-course-hero__description">
                                {{ $lesson->localized('description') }}
                            </p>
                        @endif

                    </div>

                </div>


                {{-- Progress panel --}}
                <div class="lq-course-hero__progress">

                    <div class="lq-course-hero__progress-head">
                        <span class="lq-course-hero__progress-label">
                            {{ __('localization.your_progress') }}
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
                            {{ __('localization.lesson_completed_amazing') }}
                        @else
                            {{ __('localization.activities_completed_count', ['completed' => $this->completedActivities, 'total' => $this->totalActivities]) }}
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
                label="{{ __('localization.activities') }}"
                variant="primary"
            />

            <x-lq.stat-chip
                icon="✅"
                :value="$this->completedActivities"
                label="{{ __('localization.completed') }}"
                variant="success"
            />

            <x-lq.stat-chip
                icon="⭐"
                :value="$lesson->xp_reward ?? 0"
                label="{{ __('localization.lesson_xp') }}"
                variant="reward"
            />

        </div>


        {{-- Activities --}}
        <x-lq.section-title subtitle="{{ __('localization.finish_every_activity') }}">
            🧩 {{ __('localization.activities') }}
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
                                'quiz' => ['❓', __('localization.quiz') ],
                                'story' => ['📖', __('localization.story') ],
                                'project' => ['🛠️', __('localization.project') ],
                                'ai_chat' => ['🤖', __('localization.chat_with_ai') ],
                                'coding_challenge' => ['💻', __('localization.coding_challenge') ],
                                'video' => ['🎬', __('localization.video') ],
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
                                        {{ $activity->localized('title') }}
                                    </h3>

                                    @if ($isNext)
                                        <x-lq.badge variant="primary" size="sm">
                                            {{ __('localization.up_next') }}
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
                                        ✓ {{ __('localization.completed') }}
                                    </x-lq.badge>

                                    <x-lq.button
                                        tag="a"
                                        href="{{ route('learn.activity', $activity) }}"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        {{ __('localization.review') }}
                                    </x-lq.button>

                                @elseif ($isNext)
                                    <x-lq.button
                                        tag="a"
                                        href="{{ route('learn.activity', $activity) }}"
                                        variant="primary"
                                        size="sm"
                                    >
                                        {{ __('localization.start') }}
                                    </x-lq.button>

                                @else
                                    <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">
                                        {{ __('localization.complete_previous_activity') }}
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
                title="{{ __('localization.activities_on_the_way') }}"
                message="{{ __('localization.no_activities_yet') }}"
                :action="__('localization.back_to_course')"
                href="{{ route('learn.course', $course) }}"
            />

        @endif


        {{-- Finished: send them onward --}}
        @if ($this->isCompleted)

            <x-lq.glass-card padding="lg">

                <div class="mx-auto max-w-md space-y-4 text-center">

                    <div class="text-5xl">🎉</div>

                    <h2 class="text-2xl font-extrabold text-slate-800">
                        {{ __('localization.lesson_complete') }}
                    </h2>

                    <p class="text-slate-600">
                        {{ __('localization.lesson_finish_message') }}
                    </p>

                    <x-lq.button
                        tag="a"
                        href="{{ route('learn.course', $course) }}"
                        variant="primary"
                    >
                        🗺️ {{ __('localization.back_to_adventure_map') }}
                    </x-lq.button>

                </div>

            </x-lq.glass-card>

        @endif

    </x-child.layout.section-stack>

</x-child.layout.app-shell>