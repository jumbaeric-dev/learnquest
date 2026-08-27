@props([
    'value' => 0,
    'max' => 100,
    'label' => null,
    'valueLabel' => null,
    'color' => 'primary',
])

@php
    $maxValue = max(1, (float) $max);

    $percentage = max(
        0,
        min(
            100,
            ((float) $value / $maxValue) * 100
        )
    );

    $displayValue = $valueLabel ?? "{$value}%";
@endphp

<div {{ $attributes->class(['lq-progress-summary']) }}>

    <div class="lq-progress-summary__header">

        @if($label)
            <span class="lq-progress-summary__label">
                {{ $label }}
            </span>
        @endif

        <strong class="lq-progress-summary__value">
            {{ $displayValue }}
        </strong>

    </div>

    <x-lq.progress-bar
        :value="$value"
        :max="$max"
        :color="$color"
        :show-label="false"
    />

</div>