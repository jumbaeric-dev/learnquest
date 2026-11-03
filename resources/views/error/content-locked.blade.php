<x-child.layout.guest-layout title="Premium adventure">
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-900 px-4 py-10 text-white">
        <x-lq.glass-card class="w-full max-w-md rounded-3xl border border-slate-700/50 bg-slate-800/60 p-8 text-center shadow-2xl">
            <span class="text-6xl" aria-hidden="true">🔒</span>

            <h1 class="mt-3 text-2xl font-extrabold">This adventure is part of Premium</h1>

            <p class="mt-2 text-slate-300">
                Ask a grown-up to unlock it. They can do that in the parent area.
            </p>

            <a
                href="{{ route('child.dashboard') }}"
                class="mt-6 inline-flex rounded-2xl bg-gradient-to-r from-cyan-500 to-purple-600 px-6 py-3 font-bold text-white">
                Back to my adventures
            </a>

            @if (auth()->check() && auth()->user()->isParent())
            <p class="mt-4 text-sm">
                <a href="{{ route('parent.billing') }}" class="font-semibold text-cyan-300 hover:text-cyan-200">
                    Manage plan
                </a>
            </p>
            @endif
        </x-lq.glass-card>
    </div>
</x-child.layout.guest-layout>