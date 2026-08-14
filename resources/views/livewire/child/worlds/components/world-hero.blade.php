<div class="relative overflow-hidden rounded-[2rem]">

    {{-- World Hero --}}
    <div
        class="relative overflow-hidden rounded-[2rem]
               border border-white/70
               bg-white/95
               p-5 shadow-2xl
               backdrop-blur-xl
               sm:p-6"
    >

        {{-- Decorative glow --}}
        <div
            class="pointer-events-none absolute -right-16 -top-16
                   h-40 w-40 rounded-full
                   bg-cyan-300/20 blur-3xl">
        </div>

        <div
            class="pointer-events-none absolute -bottom-20 -left-16
                   h-40 w-40 rounded-full
                   bg-indigo-300/20 blur-3xl">
        </div>


        {{-- Back to Explore --}}
        <div class="relative z-10">

            <a
                href="{{ url('/child/explore') }}"
                class="inline-flex items-center gap-2
                       text-sm font-semibold
                       text-slate-500
                       transition
                       hover:text-indigo-600"
            >
                <span>←</span>
                <span>Explore Worlds</span>
            </a>

        </div>


        {{-- World Identity --}}
        <div
            class="relative z-10 mt-6
                   flex flex-col items-center text-center"
        >

            {{-- World Icon --}}
            <div
                class="flex h-24 w-24 items-center justify-center
                       rounded-[2rem]
                       bg-gradient-to-br
                       from-indigo-500
                       to-violet-600
                       text-5xl
                       shadow-xl
                       ring-8 ring-indigo-100"
            >
                {{ $world['icon'] ?? '🌍' }}
            </div>


            {{-- Title --}}
            <h1
                class="mt-5 text-3xl font-black tracking-tight
                       text-slate-900 sm:text-4xl"
            >
                {{ $world['title'] ?? 'Learning World' }}
            </h1>


            {{-- Description --}}
            @if(!empty($world['description']))

                <p
                    class="mt-2 max-w-xl
                           text-sm leading-relaxed
                           text-slate-600 sm:text-base"
                >
                    {{ $world['description'] }}
                </p>

            @endif

        </div>


        {{-- World Stats --}}
        <div
            class="relative z-10 mt-6
                   grid grid-cols-2 gap-3
                   sm:grid-cols-3"
        >

            {{-- Courses --}}
            <div
                class="rounded-2xl
                       bg-slate-50
                       px-4 py-3
                       text-center"
            >

                <div class="text-xl">
                    📚
                </div>

                <div class="mt-1 text-sm font-bold text-slate-900">
                    {{ $world['courses_count'] ?? 0 }}
                </div>

                <div class="text-xs text-slate-500">
                    Courses
                </div>

            </div>


            {{-- Difficulty --}}
            <div
                class="rounded-2xl
                       bg-slate-50
                       px-4 py-3
                       text-center"
            >

                <div class="text-xl">
                    ⭐
                </div>

                <div class="mt-1 text-sm font-bold text-slate-900">
                    {{ $world['difficulty'] ?? 'Beginner' }}
                </div>

                <div class="text-xs text-slate-500">
                    Difficulty
                </div>

            </div>


            {{-- Level --}}
            <div
                class="col-span-2
                       rounded-2xl
                       bg-slate-50
                       px-4 py-3
                       text-center
                       sm:col-span-1"
            >

                <div class="text-xl">
                    🏆
                </div>

                <div class="mt-1 text-sm font-bold text-slate-900">
                    Level {{ $world['level'] ?? 1 }}
                </div>

                <div class="text-xs text-slate-500">
                    Your Level
                </div>

            </div>

        </div>


        {{-- Progress --}}
        <div class="relative z-10 mt-6">

            <div class="mb-2 flex items-center justify-between">

                <span class="text-sm font-bold text-slate-800">
                    World Progress
                </span>

                <span class="text-sm font-bold text-indigo-600">
                    {{ $world['progress'] ?? 0 }}%
                </span>

            </div>

            <x-lq.progress-bar
                :value="$world['progress'] ?? 0"
                :max="100"
                :show-label="false"
                color="purple"
            />

        </div>


        {{-- XP + Continue --}}
        <div
            class="relative z-10 mt-6
                   flex flex-col gap-3
                   sm:flex-row sm:items-center
                   sm:justify-between"
        >

            {{-- XP --}}
            <div class="flex items-center gap-2">

                <span
                    class="flex h-10 w-10 items-center justify-center
                           rounded-xl bg-amber-100"
                >
                    ⚡
                </span>

                <div>

                    <div class="text-sm font-bold text-slate-900">
                        {{ number_format($world['xp'] ?? 0) }} XP
                    </div>

                    <div class="text-xs text-slate-500">
                        Earned in this world
                    </div>

                </div>

            </div>


            {{-- Continue --}}
            <x-lq.button
                type="button"
                wire:click="continueAdventure"
                class="w-full sm:w-auto"
            >
                🚀 {{ $world['action_label'] ?? 'Continue Adventure' }}
            </x-lq.button>

        </div>

    </div>

</div>