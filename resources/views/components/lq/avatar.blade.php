@props([
    'image' => null,
    'size' => 'md',
    'alt' => 'Learner avatar',
])

@php
    $sizes = [
        'sm' => 'lq-avatar--sm',
        'md' => 'lq-avatar--md',
        'lg' => 'lq-avatar--lg',
    ];
@endphp

<div
    {{ $attributes->class([
        'lq-avatar',
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    @if($image)

        <img
            src="{{ $image }}"
            alt="{{ $alt }}"
        >

    @else

        {{ $slot ?: '👨‍🚀' }}

    @endif
</div>