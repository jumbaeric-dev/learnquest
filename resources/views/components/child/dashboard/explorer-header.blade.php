<x-lq.glass-card>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <x-lq.avatar
                :src="$explorer['avatar']"
            />
            <div>
                <h2 class="text-xl font-bold">
                    {{ $explorer['name'] }}
                </h2>

                <p class="text-sm text-gray-500">
                    Level {{ $explorer['level'] }}
                </p>
            </div>
        </div>

        <div class="flex gap-2">

            <x-lq.stat-chip
                icon="heroicon-o-star"
                label="XP"
                :value="$explorer['xp']"
            />

            <x-lq.stat-chip
                icon="heroicon-o-fire"
                label="Streak"
                :value="$explorer['streak']"
            />

        </div>

    </div>

</x-lq.glass-card>