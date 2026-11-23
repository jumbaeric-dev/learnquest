<div class="px-4">

    <x-lq.glass-card>

        <div class="relative overflow-hidden">

            <div class="space-y-2">

                <h1 class="text-3xl font-bold text-gray-800">
                    🌎 {{ $title ?? __('localization.explore_universe') }}
                </h1>

                <p class="text-gray-600 text-sm">
                    {{ $subtitle ?? __('localization.discover_worlds_challenges') }}
                </p>

            </div>


            <div class="absolute right-5 top-5 text-5xl">
                🧭
            </div>

        </div>

    </x-lq.glass-card>

</div>