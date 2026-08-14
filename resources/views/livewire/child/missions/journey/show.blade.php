<div
class="
max-w-5xl
mx-auto
px-4
pb-32
space-y-8
"
>


    {{-- Mission Cover --}}

    <x-lq.glass-card>


        <div class="text-center space-y-4">


            <div class="text-6xl">

                {{ $mission['icon'] }}

            </div>


            <h1 class="text-3xl font-bold">

                {{ $mission['title'] }}

            </h1>


            <p class="text-gray-600">

                {{ $mission['description'] }}

            </p>


            <x-lq.badge>

                +{{ $mission['xp'] }} XP Reward

            </x-lq.badge>


        </div>


    </x-lq.glass-card>




    {{-- Mission Path --}}

    <div class="space-y-5">


        @foreach($mission['steps'] as $index=>$step)


            <x-lq.glass-card>


                <div class="flex gap-4 items-center">


                    <div class="text-4xl">

                        {{ $step['icon'] }}

                    </div>


                    <div>

                        <h2 class="font-bold text-xl">

                            {{ $step['title'] }}

                        </h2>


                        <p class="text-gray-600">

                            {{ $step['description'] }}

                        </p>


                    </div>


                </div>


            </x-lq.glass-card>


        @endforeach


    </div>


</div>