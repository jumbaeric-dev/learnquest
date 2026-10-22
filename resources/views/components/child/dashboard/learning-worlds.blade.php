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
        <a
            href="{{ route('child.world', $world['slug']) }}"
            class="
                    block
                    rounded-2xl
                    border
                    border-slate-200
                    bg-white/70
                    p-4
                    transition
                    hover:scale-105
                ">

            <div class="flex h-12 w-12 items-center justify-center text-4xl text-indigo-600">
                <x-lq.world-icon
                    :name="$world['icon'] ?? null"
                    fallback="🌎"
                    class="h-10 w-10"
                />
            </div>

            <h3 class="mt-3 font-bold text-lg">
                {{ $world['name'] }}
            </h3>

            <p class="text-sm text-slate-500">
                {{ $world['courses'] }}
                Courses
            </p>

        </a>
        @endforeach
    </div>

</x-lq.glass-card>