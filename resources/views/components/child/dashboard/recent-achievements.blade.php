<x-lq.glass-card animate>

    <x-lq.section-title>

        🏆 Recent Achievements

    </x-lq.section-title>


    <div class="mt-5 space-y-4">


        @forelse($achievements as $achievement)


        <div
            class="
                flex
                items-center
                gap-4
                rounded-2xl
                bg-white/60
                p-4
                ">


            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70">

                @if($achievement['icon'])

                <x-dynamic-component
                    :component="$achievement['icon']"
                    class="h-7 w-7 text-indigo-500" />

                @else

                <span class="text-3xl">
                    🏆
                </span>

                @endif

            </div>


            <div>

                <h3
                    class="font-bold">

                    {{ $achievement['name'] }}

                </h3>


                <p
                    class="text-sm text-slate-500">

                    {{ $achievement['description'] }}

                </p>


                @if($achievement['earnedAt'])

                <x-lq.badge>

                    {{ $achievement['earnedAt'] }}

                </x-lq.badge>

                @endif


            </div>


        </div>


        @empty


        <p class="text-slate-500">

            Complete activities to unlock achievements.

        </p>


        @endforelse


    </div>

</x-lq.glass-card>