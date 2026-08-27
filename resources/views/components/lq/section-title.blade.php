@props([
    'subtitle' => null,
    'size' => config('lqds.settings.section_title.defaults.size'),
    'align' => config('lqds.settings.section_title.defaults.align'),
])

@php

use App\Support\Lqds\ComponentValidator;

$settings = config('lqds.settings.section_title');

$size = ComponentValidator::resolve(
    $size,
    $settings['sizes'],
    $settings['defaults']['size']
);

$align = ComponentValidator::resolve(
    $align,
    $settings['alignments'],
    $settings['defaults']['align']
);

@endphp

<div
    {{ $attributes->class([
        'lq-section-title',
        "lq-section-title--{$size}",
        "lq-section-title--{$align}",
    ]) }}
>
    <h2 class="lq-section-title__heading">
        {{ $slot }}
    </h2>

    @if($subtitle)
        <p class="lq-section-title__subtitle">
            {{ $subtitle }}
        </p>
    @endif
</div>