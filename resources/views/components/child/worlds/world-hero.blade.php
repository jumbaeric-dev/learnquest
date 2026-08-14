<x-lq.glass-card class="overflow-hidden">

    <div
        class="relative rounded-3xl p-8 text-white"
        style="
            background:
            linear-gradient(
                135deg,
                {{ $hero['themeColor'] }},
                #111827
            );
        ">

        {{-- Background Artwork --}}
        @if($hero['coverImage'])
        <img
            src="{{ asset('storage/' . $hero['coverImage']) }}"
            alt="{{ $hero['name'] }}"
            class="absolute inset-0 h-full w-full object-cover opacity-20">
        @endif

        <div class="relative z-10">

            {{-- Title --}}
            <h1 class="text-4xl font-black">
                {{ $hero['name'] }}
            </h1>

            {{-- Tagline --}}
            @if($hero['tagline'])
            <p class="mt-2 text-lg text-white/90">
                {{ $hero['tagline'] }}
            </p>
            @endif

            {{-- Story --}}
            @if($hero['storyIntro'])
            <p class="mt-6 max-w-3xl leading-relaxed text-white/80">
                {{ $hero['storyIntro'] }}
            </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">

                <x-lq.stat-chip
                    icon="📚"
                    label="Courses"
                    :value="$hero['courses']" />

                <x-lq.stat-chip
                    icon="⭐"
                    label="Difficulty"
                    :value="$hero['difficulty']" />

                @if($hero['heroCharacter'])

                <x-lq.stat-chip
                    icon="🧙"
                    label="Guide"
                    :value="$hero['heroCharacter']" />

                @endif

            </div>

            <div class="mt-8">

                <x-lq.button
                    size="lg"
                    color="white"
                    icon="heroicon-o-play">
                    Continue Journey
                </x-lq.button>

            </div>

        </div>

    </div>

</x-lq.glass-card>