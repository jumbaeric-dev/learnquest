<div
    x-data="{ open: false }"
    class="fixed bottom-28 right-6 z-[100]">

    {{-- Actions --}}
    <div
        x-show="open"
        x-transition.opacity.duration.200ms
        x-transition.scale.origin.bottom.duration.200ms

        @click.outside="open = false"
        @keydown.escape.window="open = false"

        class="mb-4 flex flex-col items-end gap-3">

        @foreach($actions as $action)

        <button
            wire:click="execute('{{ $action['action'] }}')"
            @click="open = false"
            class="
                    flex
                    items-center
                    gap-3
                    rounded-full
                    bg-white
                    px-5
                    py-3
                    shadow-xl
                    transition
                    hover:scale-105
                ">

            <span class="text-xl">
                {{ $action['icon'] }}
            </span>

            <span>
                {{ $action['title'] }}
            </span>

        </button>

        @endforeach

    </div>

    {{-- Main FAB --}}
    <button
        @click="open = ! open"
        class="
            flex
            h-16
            w-16
            items-center
            justify-center
            rounded-full
            bg-indigo-600
            text-3xl
            text-white
            shadow-2xl
            transition
            duration-300
            hover:scale-110
        "
        :class="{ 'rotate-45' : open }">
        ✨
    </button>

</div>