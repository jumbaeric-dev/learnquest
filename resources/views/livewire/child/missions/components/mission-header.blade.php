<x-lq.glass-card>

    <div class="space-y-5">

        {{-- Title --}}
        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                🚀 Missions
            </h1>

            <p class="text-gray-600 mt-2">
                Your next adventure awaits,
                {{ $childName }}!
            </p>

        </div>

        {{-- Explorer Stats --}}
        <div class="flex flex-wrap gap-3">

            <x-lq.stat-chip
                icon="⭐"
                label="Level"
                :value="$level"
            />

            <x-lq.stat-chip
                icon="✨"
                label="XP"
                :value="$xp"
            />

            <x-lq.stat-chip
                icon="🔥"
                label="Streak"
                :value="$streak . ' days'"
            />

        </div>

    </div>

</x-lq.glass-card>