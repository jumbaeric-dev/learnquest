<x-child.layout.app-shell
    :theme="$theme"
    :explorer="$explorer"
    active="courses"
>

    @php
        $content = $activity->content ?? [];

        $options = is_array($content['options'] ?? null)
            ? $content['options']
            : [];

        $hasQuestion = ! empty($content['question']);

        $passScore = config('learnquest.activity_pass_score', 70);

        $completed = (bool) $progress?->completed;

        $correctIndex = isset($content['answer'])
            ? (int) $content['answer']
            : null;

        [$typeIcon, $typeLabel] = match ($activity->activity_type) {
            'quiz' => ['❓', 'Quiz'],
            'story' => ['📖', 'Story'],
            'project' => ['🛠️', 'Project'],
            'ai_chat' => ['🤖', 'Chat with AI'],
            'coding_challenge' => ['💻', 'Coding Challenge'],
            'video' => ['🎬', 'Video'],
            default => ['🎯', ucfirst(str_replace('_', ' ', (string) $activity->activity_type))],
        };

        $body = $content['instructions']
            ?? $content['story_text']
            ?? $content['project_description']
            ?? $content['challenge']
            ?? $content['goal']
            ?? null;
    @endphp

    <x-child.layout.section-stack spacing="relaxed">

        {{-- Back link --}}
        <div>
            <x-lq.button
                tag="a"
                href="{{ route('learn.lesson', $activity->lesson) }}"
                variant="secondary"
                size="sm"
            >
                ← Back to Lesson
            </x-lq.button>
        </div>


        {{-- Activity hero --}}
        <x-lq.glass-card
            variant="hero"
            padding="lg"
            class="lq-course-hero"
        >
            <div class="lq-course-hero__inner">

                <div class="lq-course-hero__main">

                    <div class="lq-course-hero__icon" aria-hidden="true">
                        {{ $typeIcon }}
                    </div>

                    <div class="lq-course-hero__body">

                        <div class="lq-course-hero__tags">

                            <span class="lq-course-hero__tag">
                                📘 {{ $activity->lesson->title }}
                            </span>

                            <span class="lq-course-hero__tag">
                                {{ $typeIcon }} {{ $typeLabel }}
                            </span>

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $activity->title }}
                        </h1>

                    </div>

                </div>


                {{-- Reward panel --}}
                <div class="lq-course-hero__progress">

                    <div class="lq-course-hero__progress-head">
                        <span class="lq-course-hero__progress-label">
                            Reward
                        </span>

                        <strong class="lq-course-hero__progress-value">
                            ⭐ {{ $activity->xp_reward }} XP
                        </strong>
                    </div>

                    <p class="lq-course-hero__meta">
                        @if ($completed)
                            ✓ Completed — great job!
                        @elseif ($hasQuestion)
                            Score {{ $passScore }}% or more to earn it.
                        @else
                            Finish the activity to earn it.
                        @endif
                    </p>

                </div>

            </div>

        </x-lq.glass-card>


        {{-- Quiz --}}
        @if ($hasQuestion)

            <x-lq.glass-card padding="lg" class="space-y-6">

                <div class="space-y-2">
                    <x-lq.badge variant="primary" size="sm">
                        Question
                    </x-lq.badge>

                    <h2 class="text-2xl font-extrabold leading-snug text-slate-800">
                        {{ $content['question'] }}
                    </h2>
                </div>


                {{-- Options are hidden after a failed attempt so the answer is not revealed --}}
                @if (! $submitted || $completed)

                    <div class="space-y-3">

                        @foreach ($options as $index => $option)

                            @php
                                $label = is_array($option)
                                    ? ($option['option'] ?? $option['label'] ?? $option['text'] ?? '')
                                    : $option;

                                $isCorrect = $submitted && $correctIndex === $index;
                            @endphp

                            <button
                                type="button"
                                wire:key="option-{{ $index }}"
                                wire:click="submitOption({{ $index }})"
                                wire:loading.attr="disabled"
                                wire:target="submitOption"
                                @disabled($submitted)
                                @class([
                                    'group flex w-full items-center gap-4 rounded-2xl border-2 p-4 text-left font-semibold transition',
                                    'cursor-pointer border-slate-200 bg-white text-slate-700 hover:-translate-y-0.5 hover:border-[color:var(--lq-theme-primary)] hover:shadow-md' => ! $submitted,
                                    'border-emerald-400 bg-emerald-50 text-emerald-800' => $isCorrect,
                                    'border-slate-200 bg-white text-slate-500 opacity-60' => $submitted && ! $isCorrect,
                                ])
                            >

                                <span
                                    @class([
                                        'flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-extrabold transition',
                                        'bg-slate-100 text-slate-500 group-hover:bg-[color:var(--lq-theme-primary)] group-hover:text-white' => ! $submitted,
                                        'bg-emerald-500 text-white' => $isCorrect,
                                        'bg-slate-100 text-slate-400' => $submitted && ! $isCorrect,
                                    ])
                                >
                                    @if ($isCorrect)
                                        ✓
                                    @else
                                        {{ chr(65 + $loop->index) }}
                                    @endif
                                </span>

                                <span class="flex-1">
                                    {{ $label }}
                                </span>

                            </button>

                        @endforeach

                    </div>

                @endif

            </x-lq.glass-card>

        @else

            {{-- Story / project / challenge / generic activity --}}
            <x-lq.glass-card padding="lg" class="space-y-4">

                <x-lq.badge variant="primary" size="sm">
                    {{ $typeIcon }} {{ $typeLabel }}
                </x-lq.badge>

                <p class="text-lg leading-relaxed text-slate-700">
                    {{ $body ?? 'Complete this activity to continue your learning journey.' }}
                </p>

                @if (! empty($content['difficulty']))
                    <x-lq.badge variant="warning" size="sm">
                        Difficulty: {{ ucfirst($content['difficulty']) }}
                    </x-lq.badge>
                @endif

                @if (! $submitted)
                    <div class="pt-2">
                        <x-lq.button
                            type="button"
                            wire:click="complete"
                            wire:loading.attr="disabled"
                            wire:target="complete"
                            variant="primary"
                        >
                            ✅ Complete Activity
                        </x-lq.button>
                    </div>
                @endif

            </x-lq.glass-card>

        @endif


        {{-- Result --}}
        @if ($submitted)

            <x-lq.glass-card padding="lg">

                <div class="mx-auto max-w-md space-y-5 text-center">

                    @if ($completed)

                        <div class="text-6xl">🎉</div>

                        <h2 class="text-2xl font-extrabold text-slate-800">
                            Activity Complete!
                        </h2>

                        <div class="grid grid-cols-2 gap-4">

                            <x-lq.stat-chip
                                icon="🎯"
                                :value="($progress->score ?? 0) . '%'"
                                label="Score"
                                variant="success"
                            />

                            <x-lq.stat-chip
                                icon="⭐"
                                :value="($progress->xp_earned ?? 0) . ' XP'"
                                label="XP Earned"
                                variant="reward"
                            />

                        </div>

                        <x-lq.button
                            tag="a"
                            href="{{ route('learn.lesson', $activity->lesson) }}"
                            variant="primary"
                        >
                            ← Back to Lesson
                        </x-lq.button>

                    @else

                        <div class="text-6xl">🚀</div>

                        <h2 class="text-2xl font-extrabold text-slate-800">
                            Keep Exploring!
                        </h2>

                        <div class="mx-auto max-w-[12rem]">
                            <x-lq.stat-chip
                                icon="🎯"
                                :value="($progress?->score ?? $score ?? 0) . '%'"
                                label="Your Score"
                                variant="warning"
                            />
                        </div>

                        <p class="text-slate-600">
                            You need {{ $passScore }}% to complete this activity.
                            You can do it — give it another go!
                        </p>

                        <x-lq.button
                            type="button"
                            wire:click="retry"
                            variant="primary"
                        >
                            🔄 Try Again
                        </x-lq.button>

                    @endif

                </div>

            </x-lq.glass-card>

        @endif

    </x-child.layout.section-stack>

</x-child.layout.app-shell>