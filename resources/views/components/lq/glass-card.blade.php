@props([
    'padding' => 'md',
    'radius' => 'xl',
    'variant' => 'glass',
])

@php
    $classes = [
        'sm' => 'lq-card--padding-sm',
        'md' => 'lq-card--padding-md',
        'lg' => 'lq-card--padding-lg',
    ];

    $radii = [
        'lg' => 'lq-card--radius-lg',
        'xl' => 'lq-card--radius-xl',
        'full' => 'lq-card--radius-full',
    ];
@endphp

<div
    {{ $attributes->class([
        'lq-card',
        "lq-card--{$variant}",
        $classes[$padding] ?? $classes['md'],
        $radii[$radius] ?? $radii['xl'],
    ]) }}
>
    {{ $slot }}
</div>