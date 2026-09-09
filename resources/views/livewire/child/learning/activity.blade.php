<div class="space-y-8">

    {{-- Activity header --}}
    <section class="space-y-3">
        <div class="text-sm text-gray-500">
            {{ $activity->lesson->title }}
        </div>

        <h1 class="text-3xl font-bold text-gray-900">
            {{ $activity->title }}
        </h1>

        <div class="flex gap-4 text-sm font-medium text-gray-600">
            <span class="flex items-center gap-1">
                ⭐ {{ $activity->xp_reward ?? 25 }} XP
            </span>
            <span class="flex items-center gap-1">
                🎯 {{ ucfirst($activity->activity_type) }}
            </span>
        </div>
    </section>

    {{-- Question / Content --}}
    @php
        $content = $activity->content ?? [];
        $options = $content['options'] ?? [];
    @endphp

    @if (!empty($content['question']))
        <section class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-900">
                {{ $content['question'] }}
            </h2>

            @if (!$submitted && is_array($options))
                <div class="space-y-3">
                    @foreach ($options as $index => $option)
                        @php
                            $label = is_array($option)
                                ? ($option['option'] ?? $option['label'] ?? $option['text'] ?? '')
                                : $option;
                        @endphp

                        <button
                            type="button"
                            wire:click="submitOption({{ $index }})"
                            wire:loading.attr="disabled"
                            class="block w-full rounded-xl border-2 border-gray-200 bg-white p-4 text-left transition hover:border-blue-400 hover:bg-blue-50 disabled:opacity-50"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    {{-- Generic activity (non-quiz) --}}
    @if (empty($content['question']) && !$submitted)
        <section class="rounded-xl border-2 border-gray-200 bg-white p-6">
            @if (!empty($content['instructions']))
                <p class="text-gray-700">{{ $content['instructions'] }}</p>
            @else
                <p class="text-gray-700">Complete this activity to continue your learning journey.</p>
            @endif
        </section>

        <button
            type="button"
            wire:click="complete"
            wire:loading.attr="disabled"
            class="rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
        >
            <span wire:loading.remove>Complete Activity</span>
            <span wire:loading>Completing...</span>
        </button>
    @endif

    {{-- 🎉 CELEBRATION & RESULT SECTION --}}
    @if ($submitted)
        <section class="rounded-2xl border-2 border-green-200 bg-green-50 p-6 space-y-4">
            
            @if ($progress?->completed)
                <div class="flex items-center gap-3">
                    <span class="text-4xl">🎉</span>
                    <div>
                        <h2 class="text-2xl font-bold text-green-800">Activity Complete!</h2>
                        <p class="text-green-700">Great job keeping up the momentum!</p>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    {{-- Score --}}
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-gray-900">{{ $progress->score }}%</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Score</div>
                    </div>

                    {{-- XP Earned --}}
                    <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                        <div class="text-2xl font-bold text-yellow-600">+{{ $progress->xp_earned }}</div>
                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">XP Earned</div>
                    </div>

                    {{-- Streak --}}
                    @if (!empty($result['streak']))
                        <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-orange-600">{{ $result['streak']['current_streak'] }} 🔥</div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Day Streak</div>
                        </div>
                    @endif

                    {{-- Level --}}
                    @if (!empty($result['level']))
                        <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                            <div class="text-2xl font-bold text-purple-600">{{ $result['level'] }}</div>
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Level</div>
                        </div>
                    @endif
                </div>

                {{-- New Badges Awarded --}}
                @if (!empty($result['badges']) && count($result['badges']) > 0)
                    <div class="mt-4 rounded-xl bg-yellow-100 p-4 border border-yellow-300">
                        <h3 class="font-semibold text-yellow-800 mb-2">🏆 New Badges Unlocked!</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($result['badges'] as $badge)
                                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-200 px-3 py-1 text-sm font-medium text-yellow-800">
                                    {{ $badge->name ?? 'Badge' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

            @else
                {{-- Not Passed Yet --}}
                <div class="flex items-center gap-3">
                    <span class="text-4xl">🚀</span>
                    <div>
                        <h2 class="text-2xl font-bold text-blue-800">Keep Exploring!</h2>
                        <p class="text-blue-700">You're getting closer. Try again to master this!</p>
                    </div>
                </div>
                <p class="mt-2 text-gray-700">
                    Your score: <strong>{{ $progress?->score ?? $score }}%</strong>. 
                    You need {{ config('learnquest.activity_pass_score', 70) }}% to complete this activity.
                </p>
            @endif

            {{-- Dynamic Navigation Buttons --}}
            @if ($progress?->completed)
                @php $nextStep = $this->getNextStep(); @endphp
                
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @if ($nextStep)
                        <a
                            href="{{ $nextStep['url'] }}"
                            class="flex-1 rounded-xl bg-blue-600 px-6 py-3 text-center font-semibold text-white transition hover:bg-blue-700 shadow-sm"
                        >
                            {{ $nextStep['label'] }} {{ $nextStep['icon'] }}
                        </a>
                    @endif
                    
                    <a
                        href="{{ route('learn.lesson', $activity->lesson) }}"
                        class="rounded-xl border-2 border-gray-200 bg-white px-6 py-3 text-center font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        ← Review Lesson
                    </a>
                </div>
            @endif
        </section>
    @endif

</div>