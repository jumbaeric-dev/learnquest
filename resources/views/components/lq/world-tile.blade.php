@props([
    'icon' => '🌎',
    'title',
    'description' => null,
    'progress' => 0,
    'xp' => 0,
    'badge' => null,
    'locked' => false,
    'showAction' => true,
    'gradient' => null,
])

@php
    $progress = max(0, min(100, (float) $progress));
@endphp

<article
    {{ $attributes->class([
        'lq-world-tile',
        'lq-world-tile--locked' => $locked,
    ]) }}
>
    {{-- Hero --}}
    <div class="lq-world-tile__hero">

        <div class="lq-world-tile__icon">
            {{ $icon }}
        </div>

        @if($badge)
            <div class="absolute right-3 top-3">
                <x-lq.badge variant="achievement">
                    {{ $badge }}
                </x-lq.badge>
            </div>
        @endif

    </div>


    {{-- Content --}}
    <div class="lq-world-tile__content">

        <h3 class="lq-world-tile__title">
            {{ $title }}
        </h3>

        @if($description)
            <p class="lq-world-tile__description">
                {{ $description }}
            </p>
        @endif


        {{-- Progress --}}
        <div class="mt-5">

            <div class="lq-progress-line">
                <span>Progress</span>

                <span>
                    {{ number_format($progress) }}%
                </span>
            </div>

            <div class="lq-progress">
                <span style="width: {{ $progress }}%;"></span>
            </div>

        </div>


        {{-- Footer --}}
        <div class="lq-world-tile__footer">

            <x-lq.badge variant="achievement">
                ⭐ {{ number_format($xp) }} XP
            </x-lq.badge>


            @if($showAction)

                @if($locked)

                    <x-lq.button
                        variant="secondary"
                        size="sm"
                        disabled
                    >
                        🔒 Locked
                    </x-lq.button>

                @else

                    {{ $slot }}

                @endif

            @endif

        </div>

    </div>

</article>