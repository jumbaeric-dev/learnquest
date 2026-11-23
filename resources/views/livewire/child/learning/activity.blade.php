<x-child.layout.app-shell
    :theme="$theme"
    :explorer="$explorer"
    active="courses"
>

    @php
        $content = $activity->localizedContent();

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
            'quiz' => ['❓', __('localization.quiz') ],
            'story' => ['📖', __('localization.story') ],
            'project' => ['🛠️', __('localization.project') ],
            'ai_chat' => ['🤖', __('localization.chat_with_ai') ],
            'coding_challenge' => ['💻', __('localization.coding_challenge') ],
            'video' => ['🎬', __('localization.video') ],
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
                ← {{ __('localization.back_to_lesson') }}
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
                                📘 {{ $activity->lesson->localized('title') }}
                            </span>

                            <span class="lq-course-hero__tag">
                                {{ $typeIcon }} {{ $typeLabel }}
                            </span>

                        </div>

                        <h1 class="lq-course-hero__title">
                            {{ $activity->localized('title') }}
                        </h1>

                    </div>

                </div>


                {{-- Reward panel --}}
                <div class="lq-course-hero__progress">

                    <div class="lq-course-hero__progress-head">
                        <span class="lq-course-hero__progress-label">
                            {{ __('localization.reward') }}
                        </span>

                        <strong class="lq-course-hero__progress-value">
                            ⭐ {{ $activity->xp_reward }} XP
                        </strong>
                    </div>

                    <p class="lq-course-hero__meta">
                        @if ($completed)
                            {{ __('localization.completed_great_job') }}
                        @elseif ($hasQuestion)
                            {{ __('localization.score_threshold', ['score' => $passScore]) }}
                        @else
                            {{ __('localization.finish_activity_reward') }}
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
                        {{ __('localization.question') }}
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
                    {{ $body ?? __('localization.complete_activity_prompt') }}
                </p>

                @if (! empty($content['difficulty']))
                    <x-lq.badge variant="warning" size="sm">
                        {{ __('localization.difficulty') }}: {{ ucfirst($content['difficulty']) }}
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
                            {{ __('localization.complete_activity') }}
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
                            {{ __('localization.activity_complete') }}
                        </h2>

                        <div class="grid grid-cols-2 gap-4">

                            <x-lq.stat-chip
                                icon="🎯"
                                :value="($progress->score ?? 0) . '%'"
                                label="{{ __('localization.score') }}"
                                variant="success"
                            />

                            <x-lq.stat-chip
                                icon="⭐"
                                :value="($progress->xp_earned ?? 0) . ' XP'"
                                label="{{ __('localization.xp_earned') }}"
                                variant="reward"
                            />

                        </div>

                        <x-lq.button
                            tag="a"
                            href="{{ route('learn.lesson', $activity->lesson) }}"
                            variant="primary"
                        >
                            ← {{ __('localization.back_to_lesson') }}
                        </x-lq.button>

                    @else

                        <div class="text-6xl">🚀</div>

                        <h2 class="text-2xl font-extrabold text-slate-800">
                            {{ __('localization.keep_exploring') }}
                        </h2>

                        <div class="mx-auto max-w-[12rem]">
                            <x-lq.stat-chip
                                icon="🎯"
                                :value="($progress?->score ?? $score ?? 0) . '%'"
                                label="{{ __('localization.your_score') }}"
                                variant="warning"
                            />
                        </div>

                        <p class="text-slate-600">
                            {{ __('localization.need_score_retry', ['score' => $passScore]) }}
                        </p>

                        <x-lq.button
                            type="button"
                            wire:click="retry"
                            variant="primary"
                        >
                            {{ __('localization.try_again') }}
                        </x-lq.button>

                    @endif

                </div>

            </x-lq.glass-card>

        @endif

    </x-child.layout.section-stack>

</x-child.layout.app-shell>