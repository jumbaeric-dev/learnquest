<div class="space-y-4">


    <h2 class="text-xl font-bold text-gray-800">

        🧭 Explore Missions

    </h2>



    <div class="flex gap-3 overflow-x-auto pb-2">


        @foreach($categories as $category)


            <button

                wire:click="selectCategory('{{ $category['name'] }}')"

                class="
                    flex items-center gap-2
                    px-4 py-3
                    rounded-2xl
                    whitespace-nowrap
                    transition
                    border
                    shadow-sm

                    @if($selectedCategory === $category['name'])
                        bg-indigo-500
                        text-white
                    @else
                        bg-white/70
                        text-gray-700
                    @endif
                "

            >

                <span class="text-xl">

                    {{ $category['icon'] }}

                </span>


                <span class="font-semibold">

                    {{ $category['label'] ?? $category['name'] }}

                </span>


            </button>


        @endforeach


    </div>


</div>