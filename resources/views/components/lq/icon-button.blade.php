@props([
    'size' => 'md',
    'badge' => null,
    'type' => 'button',
])

@php
    $sizes = [
        'sm' => 'lq-icon-button--sm',
        'md' => 'lq-icon-button--md',
        'lg' => 'lq-icon-button--lg',
    ];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([
        'lq-icon-button',
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    {{ $slot }}

    @if($badge)
        <span class="lq-icon-button__badge">
            {{ $badge }}
        </span>
    @endif
</button>