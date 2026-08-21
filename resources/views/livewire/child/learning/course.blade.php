<div class="space-y-8">

    {{-- Course header --}}
    <section>
        <div class="space-y-3">
            @if ($course->subject)
                <p class="text-sm font-medium">
                    {{ $course->subject->name }}
                </p>
            @endif

            <h1 class="text-3xl font-bold">
                {{ $course->title }}
            </h1>

            @if ($course->description)
                <p class="max-w-2xl">
                    {{ $course->description }}
                </p>
            @endif
        </div>
    </section>


    {{-- Course progress --}}
    <section>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">
                    Your Progress
                </h2>

                <p>
                    {{ $this->completedLessons }}
                    of
                    {{ $this->totalLessons }}
                    lessons completed
                </p>
            </div>

            <strong>
                {{ $this->progressPercentage }}%
            </strong>
        </div>

        <div
            class="mt-3 h-3 w-full overflow-hidden rounded-full"
            aria-label="Course progress"
        >
            <div
                class="h-full rounded-full"
                style="width: {{ $this->progressPercentage }}%"
            ></div>
        </div>
    </section>


    {{-- Adventure map --}}
    <section class="space-y-6">

        <div>
            <h2 class="text-2xl font-bold">
                Adventure Map
            </h2>

            <p>
                Complete each lesson to continue your journey.
            </p>
        </div>


        @forelse ($course->modules as $module)

            <section class="space-y-4">

                <div>
                    <h3 class="text-xl font-semibold">
                        {{ $module->title }}
                    </h3>

                    @if ($module->description)
                        <p>
                            {{ $module->description }}
                        </p>
                    @endif
                </div>


                <div class="space-y-3">

                    @forelse ($module->lessons as $lesson)

                        @php
                            $completed = $this->isLessonCompleted($lesson->id);
                        @endphp

                        <article
                            wire:key="lesson-{{ $lesson->id }}"
                            class="rounded-xl border p-4"
                        >

                            <div class="flex items-start justify-between gap-4">

                                <div class="space-y-2">

                                    <h4 class="text-lg font-semibold">
                                        {{ $lesson->title }}
                                    </h4>

                                    @if ($lesson->description)
                                        <p>
                                            {{ $lesson->description }}
                                        </p>
                                    @endif

                                    <p class="text-sm">
                                        {{ $lesson->estimated_minutes ?? 5 }}
                                        minutes
                                        ·
                                        {{ $lesson->xp_reward }} XP
                                    </p>

                                </div>


                                <div>
                                    @if ($completed)

                                        <span>
                                            ✓ Completed
                                        </span>

                                    @else

                                        <a
                                            href="{{ route('learn.lesson', $lesson) }}"
                                        >
                                            Start Lesson
                                        </a>

                                    @endif
                                </div>

                            </div>

                        </article>

                    @empty

                        <p>
                            This module does not have any lessons yet.
                        </p>

                    @endforelse

                </div>

            </section>

        @empty

            <div>
                <h2>
                    Your adventure is being prepared 🚀
                </h2>

                <p>
                    There are no lessons in this course yet.
                </p>
            </div>

        @endforelse

    </section>

</div>