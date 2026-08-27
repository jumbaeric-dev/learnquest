@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'showLabel' => true,
])

@php
    $maxValue = max(1, (float) $max);

    $numericValue = (float) $value;

    $percentage = max(
        0,
        min(
            100,
            ($numericValue / $maxValue) * 100
        )
    );

    $allowedColors = [
        'primary',
        'cyan',
        'success',
        'warning',
        'danger',
        'reward',
    ];

    $color = in_array($color, $allowedColors, true)
        ? $color
        : 'primary';
@endphp

<div
    {{ $attributes->class([
        'lq-progress-bar',
    ]) }}
>
    <div
        class="lq-progress lq-progress--{{ $color }}"
        role="progressbar"
        aria-valuenow="{{ $numericValue }}"
        aria-valuemin="0"
        aria-valuemax="{{ $max }}"
    >
        <span
            class="lq-progress__fill"
            style="width: {{ $percentage }}%;"
        ></span>
    </div>

    @if($showLabel)
        <div class="lq-progress-bar__labels">
            <span>
                {{ number_format($numericValue) }}
            </span>

            <span>
                {{ number_format($max) }}
            </span>
        </div>
    @endif
</div>