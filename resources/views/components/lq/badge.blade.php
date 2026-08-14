@props([
    'variant' => config('lqds.settings.badge.defaults.variant'),
    'size' => config('lqds.settings.badge.defaults.size'),
])

@php

use App\Support\Lqds\ComponentValidator;

$settings = config('lqds.settings.badge');

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


<span
    {{ $attributes->class([
        'lq-badge',
        "lq-badge--{$variant}",
        "lq-badge--{$size}",
    ]) }}
>
    {{ $slot }}
</span>