<x-child.layout.app-shell
    :theme="$theme"
    :explorer="$explorer"
    active="courses">
    <x-child.layout.section-stack spacing="relaxed">

        {{-- Course hero --}}
        <x-lq.glass-card
            variant="hero"
            padding="lg"
            class="lq-course-hero">
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

                            @if ($course->subject)
                            <span class="lq-course-hero__tag">
                                🌍 {{ $course->subject->localized('name') }}
                            </span>
                            @endif

                            @if ($course->age_group)
                            <span class="lq-course-hero__tag">
                                {{ __('localization.course_age_group', ['ages' => $course->age_group]) }}
                            </span>
                            @endif

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $course->localized('title') }}
                        </h1>

                        @if ($course->localized('description'))
                        <p class="lq-course-hero__description">
                            {{ $course->localized('description') }}
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
                            style="width: {{ $this->progressPercentage }}%"></div>
                    </div>

                    <p class="lq-course-hero__meta">
                        {{ __('localization.lessons_completed_count', ['completed' => $this->completedLessons, 'total' => $this->totalLessons]) }}
                    </p>

                </div>

            </div>

        </x-lq.glass-card>


        {{-- Quick stats --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

            <x-lq.stat-chip
                icon="📦"
                :value="$course->modules->count()"
                label="{{ __('localization.modules') }}"
                variant="primary" />

            <x-lq.stat-chip
                icon="📖"
                :value="$this->totalLessons"
                label="{{ __('localization.lessons') }}"
                variant="neutral" />

            <x-lq.stat-chip
                icon="✅"
                :value="$this->completedLessons"
                label="{{ __('localization.completed') }}"
                variant="success" />

            <x-lq.stat-chip
                icon="⭐"
                :value="number_format($this->totalXp)"
                label="{{ __('localization.total_xp') }}"
                variant="reward" />

        </div>


        {{-- Adventure map --}}
        <x-lq.section-title subtitle="{{ __('localization.complete_each_lesson') }}">
            🗺️ {{ __('localization.adventure_map') }}
        </x-lq.section-title>

        @php
        $nextLessonFound = false;
        @endphp

        @forelse ($course->modules as $moduleIndex => $module)

        <x-lq.glass-card padding="lg" class="space-y-5">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[color:var(--lq-theme-soft)] text-sm font-extrabold text-[color:var(--lq-theme-primary)]">
                    {{ $moduleIndex + 1 }}
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ $module->localized('title') }}
                    </h3>

                    @if ($module->localized('description'))
                    <p class="text-sm text-slate-500">
                        {{ $module->localized('description') }}
                    </p>
                    @endif
                </div>

            </div>


            <div class="space-y-3">

                @forelse ($module->lessons as $lessonIndex => $lesson)

                @php
                $completed = $this->isLessonCompleted($lesson->id);

                $isNext = $this->isCurrentCourse && ! $completed && ! $nextLessonFound;

                if ($isNext) {
                $nextLessonFound = true;
                }
                @endphp

                <article
                    wire:key="lesson-{{ $lesson->id }}"
                    @class([ 'flex flex-col gap-4 rounded-2xl border p-4 transition hover:shadow-md sm:flex-row sm:items-center' , 'border-emerald-200 bg-emerald-50/60'=> $completed,
                    'bg-[color:var(--lq-theme-soft)]' => $isNext,
                    'border-slate-200 bg-white' => ! $completed && ! $isNext,
                    ])
                    @style([
                    'border-color: color-mix(in srgb, var(--lq-theme-primary) 35%, transparent)' => $isNext,
                    ])
                    >

                    <div
                        @class([ 'flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-lg font-bold' , 'bg-emerald-500 text-white'=> $completed,
                        'text-white' => $isNext,
                        'bg-slate-100 text-slate-400' => ! $completed && ! $isNext,
                        ])
                        @style([
                        'background-color: var(--lq-theme-primary)' => $isNext,
                        ])
                        >
                        @if ($completed)
                        ✓
                        @else
                        {{ $lessonIndex + 1 }}
                        @endif
                    </div>


                    <div class="min-w-0 flex-1 space-y-1">

                        <div class="flex flex-wrap items-center gap-2">

                            <h4 class="font-semibold text-slate-800">
                                {{ $lesson->localized('title') }}
                            </h4>

                            @if ($isNext)
                            <x-lq.badge variant="primary" size="sm">
                                {{ __('localization.up_next') }}
                            </x-lq.badge>
                            @endif

                        </div>

                        @if ($lesson->localized('description'))
                        <p class="text-sm text-slate-500">
                            {{ $lesson->localized('description') }}
                        </p>
                        @endif

                        <p class="text-xs font-medium text-slate-400">
                            ⏱ {{ $lesson->estimated_minutes ?? 5 }} minutes
                            ·
                            ⭐ {{ $lesson->xp_reward }} XP
                        </p>

                    </div>


                    <div class="flex shrink-0 flex-col items-start gap-2 sm:items-end">

                        @if ($completed)

                            <x-lq.badge variant="success">
                                ✓ {{ __('localization.completed') }}
                            </x-lq.badge>

                            <x-lq.button
                                tag="a"
                                href="{{ route('learn.lesson', $lesson) }}"
                                variant="secondary"
                                size="sm">
                                {{ __('localization.review') }}
                            </x-lq.button>

                        @elseif ($isNext)

                            <x-lq.button
                                tag="a"
                                href="{{ route('learn.lesson', $lesson) }}"
                                variant="primary"
                                size="sm">
                                ▶ Continue
                            </x-lq.button>

                        @else

                            <span class="inline-flex items-center rounded-xl bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500">
                                🔒 {{ $this->isCurrentCourse ? 'Complete the previous lesson first' : 'Start this course from the Adventure Path first' }}
                            </span>

                        @endif

                    </div>

                </article>

                @empty

                <x-lq.empty-state
                    icon="📭"
                    :title="__('localization.no_lessons_yet')"
                    :message="__('localization.module_no_lessons')" />

                @endforelse

            </div>

        </x-lq.glass-card>

        @empty

        <x-lq.empty-state
            icon="🚀"
            :title="__('localization.adventure_preparing')"
            :message="__('localization.course_no_lessons')"
            :action="__('localization.back_to_explore')"
            href="{{ route('child.explore') }}" />

        @endforelse

    </x-child.layout.section-stack>
</x-child.layout.app-shell>