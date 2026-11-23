<x-lq.glass-card animate>

    <x-lq.section-title>
        🧭 {{ __('localization.recommended_adventures') }}
    </x-lq.section-title>

    <div
        class="
        grid
        grid-cols-1
        md:grid-cols-2
        xl:grid-cols-3
        gap-6
        ">
        @forelse($adventures as $adventure)
        <div
            class="
                rounded-2xl
                bg-white/70
                p-5
                transition
                hover:scale-105
                ">

            <div class="flex items-center gap-3">

                @if(! empty($adventure['icon']))

                <x-lq.world-icon
                    :name="$adventure['icon']"
                    class="h-8 w-8 shrink-0 text-indigo-500" />

                @else

                <span class="text-3xl" aria-hidden="true">
                    🚀
                </span>

                @endif

                <h3 class="font-bold">
                    {{ $adventure['title'] }}
                </h3>
            </div>

            <p
                class="
                    mt-3
                    text-sm
                    text-slate-500
                    ">
                {{ $adventure['description'] }}
            </p>

            <x-lq.badge>
                {{ __('localization.ages', ['ages' => $adventure['ageGroup']]) }}
            </x-lq.badge>

            <x-lq.button
                wire:click="explore({{ $adventure['id'] }})">
                {{ __('localization.explore') }}
            </x-lq.button>

        </div>

        @empty

        <p class="text-slate-500">
            {{ __('localization.more_adventures') }}
        </p>

        @endforelse
    </div>

</x-lq.glass-card>