@props([
    'spacing' => 'normal',
])

@php
    $spacingClasses = match ($spacing) {
        'tight' => 'space-y-4',
        'normal' => 'space-y-6',
        'relaxed' => 'space-y-8',
        'loose' => 'space-y-10',
        default => 'space-y-6',
    };
@endphp

<div {{ $attributes->merge([
    'class' => $spacingClasses,
]) }}>
    {{ $slot }}
</div>