@props([
'title',
'subtitle' => null,
])

<x-child.layout.guest-layout :title="$title">
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-slate-900 px-4 py-10 text-white">

        <div class="pointer-events-none absolute left-1/2 top-1/4 h-96 w-96 -translate-x-1/2 -translate-y-1/2 rounded-full bg-purple-600/30 blur-3xl"></div>
        <div class="pointer-events-none absolute bottom-1/4 left-1/3 h-80 w-80 rounded-full bg-cyan-600/20 blur-3xl"></div>

        <x-lq.glass-card
            class="relative z-10 w-full max-w-md rounded-3xl border border-slate-700/50 bg-slate-800/60 p-8 shadow-2xl backdrop-blur-md">
            <div class="mb-6 text-center">
                <span class="mb-2 inline-block text-5xl">👨‍👩‍👧</span>

                <h1 class="bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-3xl font-extrabold text-transparent">
                    {{ $title }}
                </h1>

                @if($subtitle)
                <p class="mt-1 text-sm text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>

            @if (session('status'))
                <p role="status" class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('status') }}
                </p>
            @endif

            {{ $slot }}
        </x-lq.glass-card>
    </div>
</x-child.layout.guest-layout>