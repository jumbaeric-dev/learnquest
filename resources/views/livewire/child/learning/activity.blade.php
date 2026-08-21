<div class="space-y-8">

    {{-- Activity header --}}
    <section class="space-y-3">

        <div class="text-sm">
            {{ $activity->lesson->title }}
        </div>

        <h1 class="text-3xl font-bold">
            {{ $activity->title }}
        </h1>

        <div class="flex gap-4 text-sm">
            <span>
                {{ $activity->xp_reward }} XP
            </span>

            <span>
                {{ ucfirst($activity->activity_type) }}
            </span>
        </div>

    </section>


    {{-- Question --}}
    @php
        $content = $activity->content ?? [];
        $options = $content['options'] ?? [];
    @endphp

    @if (!empty($content['question']))

        <section class="space-y-6">

            <h2 class="text-xl font-semibold">
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
                            class="block w-full rounded-xl border p-4 text-left"
                        >
                            {{ $label }}
                        </button>

                    @endforeach

                </div>

            @endif

        </section>

    @endif


    {{-- Result --}}
    @if ($submitted)

        <section class="rounded-xl border p-6 space-y-4">

            @if ($progress?->completed)

                <h2 class="text-xl font-semibold">
                    🎉 Activity Complete!
                </h2>

                <p>
                    Score:
                    <strong>{{ $progress->score }}%</strong>
                </p>

                <p>
                    XP earned:
                    <strong>{{ $progress->xp_earned }} XP</strong>
                </p>

            @else

                <h2 class="text-xl font-semibold">
                    Keep Exploring 🚀
                </h2>

                <p>
                    Score:
                    <strong>{{ $progress?->score ?? $score }}%</strong>
                </p>

                <p>
                    You need
                    {{ config('learnquest.activity_pass_score', 70) }}%
                    to complete this activity.
                </p>

            @endif

        </section>

    @endif


    {{-- Generic activity --}}
    @if (
        empty($content['question']) &&
        !$submitted
    )

        <section class="rounded-xl border p-6">

            @if (!empty($content['instructions']))
                <p>
                    {{ $content['instructions'] }}
                </p>
            @else
                <p>
                    Complete this activity to continue your learning journey.
                </p>
            @endif

        </section>

        <button
            type="button"
            wire:click="complete"
            wire:loading.attr="disabled"
            class="rounded-xl border px-6 py-3"
        >
            Complete Activity
        </button>

    @endif


    {{-- Navigation --}}
    @if ($submitted && $progress?->completed)

        <section>

            <a
                href="{{ route('learn.lesson', $activity->lesson) }}"
                class="inline-block rounded-xl border px-6 py-3"
            >
                ← Back to Lesson
            </a>

        </section>

    @endif

</div>