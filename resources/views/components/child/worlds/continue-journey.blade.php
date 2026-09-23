<x-lq.glass-card>

    <div class="flex items-center justify-between gap-4">

        <div class="min-w-0">

            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">
                Continue where you left off
            </p>

            <h3 class="mt-1 truncate text-lg font-bold text-slate-800">
                {{ $journey['course'] }}
            </h3>

            <p class="truncate text-sm text-slate-500">
                {{ $journey['lesson'] }}
            </p>

            <div class="mt-3 max-w-xs">
                <div class="lq-progress">
                    <span style="width: {{ $journey['progress'] }}%;"></span>
                </div>
            </div>

        </div>

        <x-lq.button wire:click="continueJourney">
            {{ $journey['buttonText'] }}
        </x-lq.button>

    </div>

</x-lq.glass-card>
