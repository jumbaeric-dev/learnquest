<x-lq.glass-card

    class="
        transition
        duration-300
        hover:-translate-y-2
        hover:shadow-2xl
    ">

    <div class="space-y-4">


        {{-- Icon --}}
        <div class="text-4xl">

            {{ $mission['icon'] }}

        </div>



        {{-- Title --}}
        <div>

            <h3 class="text-xl font-bold text-gray-800">

                {{ $mission['title'] }}

            </h3>


            <p class="text-sm text-indigo-600">

                {{ $mission['category'] }}

            </p>

        </div>




        {{-- Description --}}
        <p class="text-gray-600 text-sm">

            {{ $mission['description'] }}

        </p>




        {{-- Stats --}}
        <div class="flex flex-wrap gap-2">


            <x-lq.stat-chip
                icon="⭐"
                label="XP"
                :value="$mission['xp']" />


            <x-lq.stat-chip
                icon="⏱"
                label="Time"
                :value="$mission['time']" />


        </div>




        {{-- Button --}}
        <x-lq.button
            wire:click="startMission">

            🚀 Start

        </x-lq.button>



    </div>


</x-lq.glass-card>