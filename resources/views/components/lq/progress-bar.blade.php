@php

$colors = [
'indigo' => 'bg-indigo-500',
'green' => 'bg-emerald-500',
'yellow' => 'bg-yellow-400',
'purple' => 'bg-purple-500',
'cyan' => 'bg-cyan-500',
];

@endphp

<div class="w-full">
    <div
        class="h-3 overflow-hidden rounded-full bg-slate-200">
        <div
            class="h-full rounded-full transition-all duration-700 {{ $colors[$color] ?? $colors['indigo'] }}"
            style="width: {{ $percentage }}%;">
        </div>
    </div>

    @if($showLabel)

    <div class="mt-2 flex justify-between text-xs text-slate-600">
        <span>{{ number_format($value) }}</span>
        <span>{{ number_format($max) }}</span>
    </div>

    @endif
</div>