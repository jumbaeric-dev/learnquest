@if($learning)

<x-lq.glass-card class="space-y-5" animate>

    <x-lq.section-title
        subtitle="Continue where you left off"
    >
        📚 Continue Learning
    </x-lq.section-title>

    <div>

        <h3 class="text-lg font-bold">

            {{ $learning['course'] }}

        </h3>

        <p class="text-slate-500">

            {{ $learning['lesson'] }}

        </p>

    </div>

    <x-lq.progress-bar
        :value="$learning['progress']"
        :max="100"
        :percentage="$learning['progress']"
    />

    <div class="flex justify-between">

        <span class="text-sm text-slate-500">

            {{ $learning['progress'] }}% Complete

        </span>

        <x-lq.button wire:click="continueLesson">

            {{ $learning['buttonText'] }}

        </x-lq.button>

    </div>

</x-lq.glass-card>

@else

<x-lq.glass-card>

    <x-lq.section-title>

        📚 Continue Learning

    </x-lq.section-title>

    <p class="text-slate-500">

        Start your first course to begin learning.

    </p>

</x-lq.glass-card>

@endif