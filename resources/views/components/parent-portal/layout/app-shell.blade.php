@props([
'active' => 'overview',
])

<div class="min-h-screen bg-slate-900 text-white">
    <header class="border-b border-white/10 bg-slate-900/80 backdrop-blur">
        <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-3">

            <a
                href="{{ route('parent.dashboard') }}"
                class="flex items-center gap-2 font-extrabold">
                <span class="text-2xl">🚀</span>

                <span class="bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent">
                    LearnQuest
                </span>

                <span class="hidden text-xs font-semibold uppercase tracking-wider text-slate-400 sm:inline">
                    Parent
                </span>
            </a>

            <nav class="flex items-center gap-1" aria-label="Parent navigation">
                <a
                    href="{{ route('parent.dashboard') }}"
                    @class([ 'rounded-xl px-3 py-2 text-sm font-semibold transition' , 'bg-white/10 text-white'=> $active === 'overview',
                    'text-slate-300 hover:bg-white/5 hover:text-white' => $active !== 'overview',
                    ])
                    @if($active === 'overview') aria-current="page" @endif
                    >
                    Overview
                </a>
                <a
                    href="{{ route('parent.children.index') }}"
                    @class([ 'rounded-xl px-3 py-2 text-sm font-semibold transition' , 'bg-white/10 text-white'=> $active === 'children', 'text-slate-300 hover:bg-white/5 hover:text-white' => $active !== 'children' ])
                    @if($active === 'children') aria-current="page" @endif
                    >
                    Children
                </a>
                <a
                    href="{{ route('parent.account') }}"
                    @class([ 'rounded-xl px-3 py-2 text-sm font-semibold transition' , 'bg-white/10 text-white'=> $active === 'account',
                    'text-slate-300 hover:bg-white/5 hover:text-white' => $active !== 'account',
                    ])
                    @if($active === 'account') aria-current="page" @endif
                    >
                    Account
                </a>
                <a
                    href="{{ route('parent.billing') }}"
                    @class([ 'rounded-xl px-3 py-2 text-sm font-semibold transition' , 'bg-white/10 text-white'=> $active === 'billing', 'text-slate-300 hover:bg-white/5 hover:text-white' => $active !== 'billing' ])
                    @if($active === 'billing') aria-current="page" @endif
                    >
                    Billing
                </a>
            </nav>

            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-slate-300 sm:inline">
                    {{ auth()->user()->name }}
                </span>

                <x-child.layout.logout />
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>
</div>