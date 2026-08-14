@php

$paddings = [
    'sm' => 'p-3',
    'md' => 'p-5',
    'lg' => 'p-8',
];

$radii = [
    'lg' => 'rounded-lg',
    'xl' => 'rounded-3xl',
    'full' => 'rounded-full',
];

@endphp

<div
    {{ $attributes->merge([
        'class' =>
        '
        bg-white/80
        backdrop-blur-xl
        border
        border-white/40
        shadow-xl
        '
        .$paddings[$padding]
        .' '
        .$radii[$radius]
    ]) }}
>

    {{ $slot }}

</div>