@props([
'href' => '#',
'full' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'lq-action',
        'lq-action--full' => $full,
    ]) }}>
    {{ $slot }}
</a>