<x-lq.glass-card animate>

    @if($nova)

        <div
            class="
            flex
            items-center
            gap-4
            "
        >

            <div
                class="
                flex
                h-16
                w-16
                items-center
                justify-center
                rounded-full
                bg-indigo-100
                "
            >

                <x-dynamic-component

                    :component="$nova['icon']"

                    class="
                    h-9
                    w-9
                    text-indigo-600
                    "

                />

            </div>



            <div>


                <h3
                    class="
                    text-lg
                    font-bold
                    "
                >

                    {{ $nova['greeting'] }}

                </h3>



                <p
                    class="
                    text-slate-500
                    "
                >

                    {{ $nova['message'] }}

                </p>


            </div>


        </div>



        <div class="mt-5">


            <x-lq.button>

                {{ $nova['buttonText'] }}

            </x-lq.button>


        </div>


    @endif


</x-lq.glass-card>