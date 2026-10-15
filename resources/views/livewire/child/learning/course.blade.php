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
                        {{ $course->subject->icon ?? '📘' }}
                    </div>

                    <div class="lq-course-hero__body">

                        <div class="lq-course-hero__tags">

                            @if ($course->subject)
                            <span class="lq-course-hero__tag">
                                🌍 {{ $course->subject->name }}
                            </span>
                            @endif

                            @if ($course->age_group)
                            <span class="lq-course-hero__tag">
                                🎯 Ages {{ $course->age_group }}
                            </span>
                            @endif

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $course->title }}
                        </h1>

                        @if ($course->description)
                        <p class="lq-course-hero__description">
                            {{ $course->description }}
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
                            style="width: {{ $this->progressPercentage }}%"></div>
                    </div>

                    <p class="lq-course-hero__meta">
                        {{ $this->completedLessons }} of {{ $this->totalLessons }} lessons completed
                    </p>

                </div>

            </div>

        </x-lq.glass-card>


        {{-- Quick stats --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

            <x-lq.stat-chip
                icon="📦"
                :value="$course->modules->count()"
                label="Modules"
                variant="primary" />

            <x-lq.stat-chip
                icon="📖"
                :value="$this->totalLessons"
                label="Lessons"
                variant="neutral" />

            <x-lq.stat-chip
                icon="✅"
                :value="$this->completedLessons"
                label="Completed"
                variant="success" />

            <x-lq.stat-chip
                icon="⭐"
                :value="number_format($this->totalXp)"
                label="Total XP"
                variant="reward" />

        </div>


        {{-- Adventure map --}}
        <x-lq.section-title subtitle="Complete each lesson to continue your journey.">
            🗺️ Adventure Map
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
                        {{ $module->title }}
                    </h3>

                    @if ($module->description)
                    <p class="text-sm text-slate-500">
                        {{ $module->description }}
                    </p>
                    @endif
                </div>

            </div>


            <div class="space-y-3">

                @forelse ($module->lessons as $lessonIndex => $lesson)

                @php
                $completed = $this->isLessonCompleted($lesson->id);

                $isNext = ! $completed && ! $nextLessonFound;

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
                                {{ $lesson->title }}
                            </h4>

                            @if ($isNext)
                            <x-lq.badge variant="primary" size="sm">
                                Up Next
                            </x-lq.badge>
                            @endif

                        </div>

                        @if ($lesson->description)
                        <p class="text-sm text-slate-500">
                            {{ $lesson->description }}
                        </p>
                        @endif

                        <p class="text-xs font-medium text-slate-400">
                            ⏱ {{ $lesson->estimated_minutes ?? 5 }} minutes
                            ·
                            ⭐ {{ $lesson->xp_reward }} XP
                        </p>

                    </div>


                    <div class="shrink-0">

                        @if ($completed)

                        <x-lq.badge variant="success">
                            ✓ Completed
                        </x-lq.badge>

                        @else

                        <x-lq.button
                            tag="a"
                            href="{{ route('learn.lesson', $lesson) }}"
                            variant="{{ $isNext ? 'primary' : 'secondary' }}"
                            size="sm">
                            {{ $isNext ? '▶ Continue' : 'Start Lesson' }}
                        </x-lq.button>

                        @endif

                    </div>

                </article>

                @empty

                <x-lq.empty-state
                    icon="📭"
                    title="No lessons yet"
                    message="This module does not have any lessons yet." />

                @endforelse

            </div>

        </x-lq.glass-card>

        @empty

        <x-lq.empty-state
            icon="🚀"
            title="Your adventure is being prepared"
            message="There are no lessons in this course yet — check back soon!"
            action="Back to Explore"
            href="{{ route('child.explore') }}" />

        @endforelse

    </x-child.layout.section-stack>
</x-child.layout.app-shell>