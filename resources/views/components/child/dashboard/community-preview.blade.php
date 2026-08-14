<x-lq.glass-card animate>


    <x-lq.section-title>

        🌍 Community

    </x-lq.section-title>



    <div class="mt-5">


        <h3 class="text-xl font-bold">

            {{ $community['title'] }}

        </h3>



        <p class="mt-2 text-slate-500">

            {{ $community['message'] }}

        </p>



        <div class="mt-5 space-y-3">


            @foreach($community['features'] as $feature)


            <div
                class="
                    flex
                    items-center
                    gap-3
                    rounded-xl
                    bg-white/60
                    p-3
                    ">


                <x-dynamic-component

                    :component="$feature['icon']"

                    class="
                        h-6
                        w-6
                        text-indigo-500
                        " />


                <span>

                    {{ $feature['text'] }}

                </span>


            </div>


            @endforeach


        </div>



        <div class="mt-5">

            <x-lq.button>

                {{ $community['buttonText'] }}

            </x-lq.button>

        </div>


    </div>


</x-lq.glass-card>