<nav
    x-data
    class="
        fixed
        bottom-0
        left-0
        right-0
        z-50
        border-t
        border-white/20
        bg-slate-950/80
        backdrop-blur-xl
    ">

    <div
        class="
            mx-auto
            flex
            max-w-2xl
            justify-around
            px-4
            py-3
        ">

        @foreach($items as $item)

        @php

        $active =
        request()->path() === trim($item['route'], '/');

        @endphp


        <a
            href="{{ $item['route'] }}"

            x-data="{ pressed:false }"

            @mousedown="pressed=true"
            @mouseup="pressed=false"

            :class="{
                    'scale-110': pressed
                }"


            class="
                    relative
                    flex
                    flex-col
                    items-center
                    gap-1
                    transition
                    duration-200
                    hover:scale-110
                "

            aria-label="{{ $item['title'] }}">


            @if($active)

            <span
                class="
                            absolute
                            -top-3
                            h-10
                            w-10
                            rounded-full
                            bg-indigo-500/30
                            blur-md
                        ">
            </span>

            @endif

            <span
                class="
                        relative
                        text-2xl
                        transition
                        duration-200
                    "

                @if($active)

                style="transform:scale(1.15)"

                @endif>

                {{ $item['icon'] }}

            </span>



            <span
                class="
                        text-xs
                        text-white/80
                    ">

                {{ $item['title'] }}

            </span>


        </a>


        @endforeach


    </div>

</nav>