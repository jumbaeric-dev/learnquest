<div class="space-y-6">

    <x-lq.glass-card>

        <div class="text-center">

            <div class="text-7xl">
                {{ $world['icon'] }}
            </div>

            <h1 class="mt-4 text-3xl font-bold text-slate-800">
                {{ $world['title'] }}
            </h1>

            <p class="mt-3 text-slate-600 max-w-md mx-auto">
                {{ $world['description'] }}
            </p>

            <div class="mt-6 flex justify-center gap-3">

                <x-lq.badge>
                    ⭐ Level {{ $world['level'] ?? 1 }}
                </x-lq.badge>

                <x-lq.badge color="yellow">
                    ⭐ {{ number_format($world['xp'] ?? 0) }} XP
                </x-lq.badge>

            </div>

            <div class="mt-6">

                <x-lq.progress-bar
                    :value="$world['progress']"
                    :max="100"
                    :show-label="false"
                    color="purple"
                />

                <p class="mt-2 text-sm text-slate-500">
                    {{ $world['progress'] ?? 0 }}% Complete
                </p>

            </div>

            <div class="mt-8">

                <x-lq.button class="w-full md:w-auto">

                    🚀 Continue Adventure

                </x-lq.button>

            </div>

        </div>

    </x-lq.glass-card>

</div>