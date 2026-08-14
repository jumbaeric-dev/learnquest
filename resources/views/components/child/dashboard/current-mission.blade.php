<div id="current-mission">
    @if($mission)

    <x-lq.glass-card class="space-y-5 animate">
        <div class="flex items-center justify-between">

            <x-lq.section-title
                subtitle="{{ $mission['course'] }}">
                🚀 Current Mission
            </x-lq.section-title>

            <x-lq.badge variant="primary">
                +{{ $mission['xpReward'] }} XP
            </x-lq.badge>

        </div>

        <div>

            <h3
                class="text-xl font-bold text-slate-900">
                {{ $mission['activity'] }}
            </h3>

            <p
                class="mt-1 text-slate-500">
                {{ $mission['lesson'] }}
            </p>

        </div>

        <x-lq.progress-bar
            :value="$mission['progress']"
            :max="100"
            :percentage="$mission['progress']" />

        <div
            class="flex items-center justify-between text-sm text-slate-500">

            <span>

                {{ $mission['estimatedMinutes'] }} min

            </span>

            <span>

                {{ $mission['progress'] }}%

            </span>

        </div>

        <x-lq.button wire:click="startMission"
            variant="primary"
            size="lg">
            {{ $mission['buttonText'] }}
        </x-lq.button>

    </x-lq.glass-card>

    @else

    <x-lq.glass-card>

        <x-lq.section-title>

            🎉 You're all caught up!

        </x-lq.section-title>

        <p class="text-slate-500 mt-2">

            Great work! Come back tomorrow for a new mission.

        </p>

    </x-lq.glass-card>

    @endif
</div>