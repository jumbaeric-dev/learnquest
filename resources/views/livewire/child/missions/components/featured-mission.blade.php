<x-lq.glass-card>
    @if($mission)
        <div class="space-y-6">

            {{-- Header --}}
            <div class="flex items-start gap-4">
                <div class="text-5xl">
                    <x-lq.world-icon :name="$mission['icon'] ?? null" class="h-14 w-14" fallback="🚀" />
                </div>

                <div>
                    <p class="text-sm text-indigo-600 font-semibold">
                        {{ __('localization.featured_adventure') }}
                    </p>

                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ $mission['title'] }}
                    </h2>

                    <p class="text-gray-600">
                        {{ $mission['category'] }}
                    </p>
                </div>
            </div>

            {{-- Description --}}
            <p class="text-gray-700">
                {{ $mission['description'] }}
            </p>

            {{-- Mission Stats --}}
            <div class="flex flex-wrap gap-3">
                <x-lq.stat-chip
                    icon="⭐"
                    :label="__('localization.reward')"
                    :value="$mission['xp'].' XP'"
                />

                <x-lq.stat-chip
                    icon="⏱"
                    :label="__('localization.time')"
                    :value="$mission['time']"
                />

                <x-lq.stat-chip
                    icon="🏆"
                    :label="__('localization.level')"
                    :value="$mission['difficulty']"
                />
            </div>

            {{-- Action --}}
            <div>
                <x-lq.button
                    wire:click="startMission"
                >
                    {{ __('localization.start_mission') }}
                </x-lq.button>
            </div>

        </div>
    @else
        <div class="text-center text-gray-500 py-6">
            {{ __('localization.no_featured_mission') }}
        </div>
    @endif
</x-lq.glass-card>