<x-lq.glass-card>

    <div class="space-y-5">

        {{-- Title --}}
        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                🚀 {{ __('localization.missions') }}
            </h1>

            <p class="text-gray-600 mt-2">
                {{ __('localization.mission_greeting', ['name' => $childName]) }}
                
            </p>

        </div>

        {{-- Explorer Stats --}}
        <div class="flex flex-wrap gap-3">

            <x-lq.stat-chip
                icon="⭐"
                :label="__('localization.level')"
                :value="$level"
            />

            <x-lq.stat-chip
                icon="✨"
                :label="__('localization.xp')"
                :value="$xp"
            />

            <x-lq.stat-chip
                icon="🔥"
                :label="__('localization.streak')"
                :value="__('localization.days', ['count' => $streak])"
            />

        </div>

    </div>

</x-lq.glass-card>