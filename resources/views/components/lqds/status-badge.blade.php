@props([
    'status' => 'Stable',
])

@php

$classes = match ($status) {

    'Stable'
        => 'bg-emerald-100 text-emerald-700',

    'Experimental'
        => 'bg-amber-100 text-amber-700',

    'Deprecated'
        => 'bg-red-100 text-red-700',

    default
        => 'bg-slate-100 text-slate-700',
};

@endphp

<span class="rounded-full px-3 py-1 text-xs font-semibold {{ $classes }}">

    {{ $status }}

</span>