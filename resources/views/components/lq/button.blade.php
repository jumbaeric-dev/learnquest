@props([
    'variant' => config('lqds.settings.button.defaults.variant'),
    'size' => config('lqds.settings.button.defaults.size'),
    'type' => config('lqds.settings.button.defaults.type'),
    'disabled' => false,
    'fullWidth' => config('lqds.settings.button.defaults.full_width'),
])

@php

use App\Support\Lqds\ComponentValidator;

$settings = config('lqds.settings.button');

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

<button
    type="{{ $type }}"
    @disabled($disabled)

    {{ $attributes->class([
        'lq-button',
        "lq-button--{$variant}",
        "lq-button--{$size}",
        'w-full' => $fullWidth,
    ]) }}
>
    {{ $slot }}
</button>