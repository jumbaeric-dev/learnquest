<x-lq.glass-card animate>

    <x-lq.section-title>
        🌍 Learning Worlds
    </x-lq.section-title>

    <div class="grid
grid-cols-2
md:grid-cols-3
xl:grid-cols-4
gap-4">
        @foreach($worlds as $world)
        <div
            class="
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white/70
                    p-4
                    transition
                    hover:scale-105
                ">

            <div class="text-4xl">
                {{ $world['icon'] ?? '🌎' }}
            </div>

            <h3 class="mt-3 font-bold text-lg">
                {{ $world['name'] }}
            </h3>

            <p class="text-sm text-slate-500">
                {{ $world['courses'] }}
                Courses
            </p>

        </div>
        @endforeach
    </div>

</x-lq.glass-card>