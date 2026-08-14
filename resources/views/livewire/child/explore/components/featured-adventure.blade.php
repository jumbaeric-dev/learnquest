<div class="px-4">

    <x-lq.glass-card>

        <div class="relative overflow-hidden">


            <div class="flex items-center gap-4">


                <div class="text-6xl">
                    {{ $adventure['icon'] }}
                </div>


                <div class="flex-1">

                    <h2 class="text-xl font-bold text-gray-800">
                        {{ $adventure['title'] }}
                    </h2>


                    <p class="text-sm text-gray-600 mt-1">
                        {{ $adventure['description'] }}
                    </p>


                    <div class="mt-3">

                        <x-lq.button wire:click="startAdventure">
                            Start Adventure 🚀
                        </x-lq.button>

                    </div>

                </div>


            </div>


            <div class="absolute right-4 bottom-3">

                <x-lq.badge>
                    +{{ $adventure['xp'] }} XP
                </x-lq.badge>

            </div>


        </div>


    </x-lq.glass-card>


</div>