@php

$sizes = [

    'sm' => 'h-10 w-10 text-lg',

    'md' => 'h-12 w-12 text-xl',

    'lg' => 'h-16 w-16 text-2xl',

];

@endphp

<button
    {{ $attributes->merge([
        'class' =>
        'relative flex items-center justify-center rounded-full
        bg-white/80 backdrop-blur-xl border border-white/40
        shadow-lg transition-all duration-200
        hover:scale-105 active:scale-95 '.$sizes[$size],
    ]) }}
>

    {{ $slot }}

    @if($badge)

        <span
            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">

            {{ $badge }}

        </span>

    @endif

</button>