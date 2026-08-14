<div class="space-y-4">


    <h2 class="text-xl font-bold text-gray-800">

        🎯 Today's Challenges

    </h2>



    <div class="space-y-3">


        @foreach($challenges as $challenge)


            <x-lq.glass-card>


                <div class="flex items-center justify-between">


                    <div class="flex items-center gap-4">


                        <div class="text-3xl">

                            {{ $challenge['icon'] }}

                        </div>



                        <div>

                            <h3 class="font-bold text-gray-800">

                                {{ $challenge['title'] }}

                            </h3>


                            <p class="text-sm text-indigo-600">

                                +{{ $challenge['xp'] }} XP

                            </p>

                        </div>


                    </div>




                    <button

                        wire:click="completeChallenge({{ $challenge['id'] }})"

                        @disabled($challenge['completed'])

                        class="
                            px-4
                            py-2
                            rounded-xl
                            font-semibold

                            @if($challenge['completed'])
                                bg-green-200
                                text-green-700
                            @else
                                bg-indigo-500
                                text-white
                            @endif

                        "

                    >

                        @if($challenge['completed'])

                            ✓ Done

                        @else

                            Complete

                        @endif


                    </button>



                </div>


            </x-lq.glass-card>


        @endforeach


    </div>


</div>