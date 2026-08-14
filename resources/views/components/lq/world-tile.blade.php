@props([])

<x-lq.glass-card
    {{ $attributes->class([
        'group overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl'
    ]) }}>

    {{-- Hero --}}
    <div class="relative">

        <div class="h-36 rounded-2xl bg-gradient-to-br {{ $gradient() }}">

            <div class="flex h-full items-center justify-center">

                <div class="text-7xl drop-shadow-lg transition-transform duration-300 group-hover:scale-110">
                    {{ $icon }}
                </div>

            </div>

            @if($badge)

            <div class="absolute top-3 right-3">

                <x-lq.badge>
                    {{ $badge }}
                </x-lq.badge>

            </div>

            @endif

        </div>

    </div>

    {{-- Content --}}
    <div class="mt-5 text-center">

        <h3 class="text-xl font-bold text-slate-800">
            {{ $title }}
        </h3>

        <p class="mt-2 text-sm text-slate-600">
            {{ $description }}
        </p>

    </div>

    {{-- Progress --}}
    <div class="mt-5">

        <div class="mb-2 flex items-center justify-between text-xs font-semibold text-slate-600">

            <span>Progress</span>

            <span>{{ $progress }}%</span>

        </div>

        <x-lq.progress-bar
            :value="$progress"
            :max="100"
            color="cyan"
            :show-label="false" />

    </div>

    {{-- Footer --}}
    <div class="mt-5 flex items-center justify-between">

        <x-lq.badge>
            ⭐ {{ $xp }} XP
        </x-lq.badge>


        @if($showAction)

        @if($locked)

        <x-lq.button disabled>
            🔒 Locked
        </x-lq.button>

        @else

        {{ $slot }}

        @endif

        @endif


    </div>

</x-lq.glass-card>