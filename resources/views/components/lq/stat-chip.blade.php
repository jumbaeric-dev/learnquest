@props([
'icon' => null,
'value',
'label',
'variant' => config('lqds.settings.stat_chip.defaults.variant'),
'size' => config('lqds.settings.stat_chip.defaults.size'),
])

@php

use App\Support\Lqds\ComponentValidator;

$settings = config('lqds.settings.stat_chip');

$variant = ComponentValidator::resolve(
$variant,
$settings['variants'],
$settings['defaults']['variant']
);

$size = ComponentValidator::resolve(
$size,
$settings['sizes'],
$settings['defaults']['size']
);

@endphp

<div
    {{ $attributes->class([
        'lq-stat-chip',
        "lq-stat-chip--{$variant}",
        "lq-stat-chip--{$size}",
    ]) }}>

    @if($icon)
    <span class="lq-stat-chip__icon">

        @if(str_starts_with($icon, 'heroicon-') || str_starts_with($icon, 'lucide-'))
        <x-dynamic-component
            :component="$icon"
            class="h-6 w-6 text-amber-500" />
        @else
        <span class="text-xl leading-none">
            {{ $icon }}
        </span>
        @endif

    </span>
    @endif

    <div class="lq-stat-chip__content">

        <span class="lq-stat-chip__value">
            {{ $value }}
        </span>

        <span class="lq-stat-chip__label">
            {{ $label }}
        </span>

    </div>

</div>