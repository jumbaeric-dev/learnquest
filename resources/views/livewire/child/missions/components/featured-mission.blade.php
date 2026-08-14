<x-lq.glass-card>

    <div class="space-y-6">


        {{-- Header --}}
        <div class="flex items-start gap-4">


            <div class="text-5xl">
                {{ $mission['icon'] }}
            </div>


            <div>

                <p class="text-sm text-indigo-600 font-semibold">
                    Featured Adventure
                </p>


                <h2 class="text-2xl font-bold text-gray-800">
                    {{ $mission['title'] }}
                </h2>


                <p class="text-gray-600">
                    {{ $mission['category'] }}
                </p>

            </div>


        </div>



        {{-- Description --}}
        <p class="text-gray-700">

            {{ $mission['description'] }}

        </p>




        {{-- Mission Stats --}}
        <div class="flex flex-wrap gap-3">


            <x-lq.stat-chip
                icon="⭐"
                label="Reward"
                :value="$mission['xp'].' XP'"
            />


            <x-lq.stat-chip
                icon="⏱"
                label="Time"
                :value="$mission['time']"
            />


            <x-lq.stat-chip
                icon="🏆"
                label="Level"
                :value="$mission['difficulty']"
            />


        </div>




        {{-- Action --}}
        <div>


            <x-lq.button
                wire:click="startMission"
            >

                🚀 Start Mission

            </x-lq.button>


        </div>



    </div>


</x-lq.glass-card>