@props([
    'variant' => config('lqds.settings.button.defaults.variant'),
    'size' => config('lqds.settings.button.defaults.size'),
    'type' => config('lqds.settings.button.defaults.type'),
    'disabled' => false,
    'fullWidth' => config('lqds.settings.button.defaults.full_width'),
    'tag' => 'button',
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

$allowedTags = [
    'button',
    'a',
];

$tag = in_array($tag, $allowedTags, true)
    ? $tag
    : 'button';

$classes = [
    'lq-button',
    "lq-button--{$variant}",
    "lq-button--{$size}",
    'lq-button--full' => $fullWidth,
];

@endphp

@if($tag === 'a')

    <a
        {{ $attributes->class($classes) }}
    >
        {{ $slot }}
    </a>

@else

    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class($classes) }}
    >
        {{ $slot }}
    </button>

@endif