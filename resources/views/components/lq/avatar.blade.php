@php

$sizes = [
    'sm' => 'h-10 w-10 text-lg',
    'md' => 'h-16 w-16 text-2xl',
    'lg' => 'h-24 w-24 text-4xl',
];

@endphp


<div
    class="
    {{ $sizes[$size] }}
    flex
    items-center
    justify-center
    rounded-full
    bg-gradient-to-br
    from-purple-500
    to-cyan-400
    font-bold
    text-white
    shadow-lg
    ">


    @if($image)

        <img
            src="{{ $image }}"
            class="h-full w-full rounded-full object-cover"
        >

    @else

        👨‍🚀

    @endif


</div>