@if($learning)

<x-lq.glass-card class="space-y-5" animate>

    <x-lq.section-title
        subtitle="{{ __('localization.continue_where_left') }}"
    >
        📚 {{ __('localization.continue_learning') }}
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

            {{ __('localization.complete_percent', ['percent' => $learning['progress']]) }}

        </span>

        <x-lq.button wire:click="continueLesson">

            {{ $learning['buttonText'] }}

        </x-lq.button>

    </div>

</x-lq.glass-card>

@else

<x-lq.glass-card>

    <x-lq.section-title>

        📚 {{ __('localization.continue_learning') }}

    </x-lq.section-title>

    <p class="text-slate-500">

        {{ __('localization.start_first_course') }}

    </p>

</x-lq.glass-card>

@endif