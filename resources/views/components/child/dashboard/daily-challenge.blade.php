<x-lq.glass-card animate>


    <x-lq.section-title>

        🎯 Daily Challenge

    </x-lq.section-title>



    @if($challenge)


        <div class="mt-5">


            <h3 class="text-xl font-bold">

                {{ $challenge['title'] }}

            </h3>



            <p class="mt-1 text-slate-500">

                {{ $challenge['description'] }}

            </p>



            <div class="mt-5">


                <x-lq.progress-bar

                    :value="$challenge['current']"

                    :max="$challenge['target']"

                    :percentage="
                        min(
                            100,
                            ($challenge['current']
                            /
                            $challenge['target'])
                            * 100
                        )
                    "

                />


            </div>



            <div
                class="
                mt-4
                flex
                items-center
                justify-between
                "
            >


                <span
                    class="text-sm text-slate-500"
                >

                    ⭐
                    +{{ $challenge['xpReward'] }}
                    XP

                </span>



                <x-lq.button>

                    {{ $challenge['buttonText'] }}

                </x-lq.button>


            </div>


        </div>


    @endif


</x-lq.glass-card>