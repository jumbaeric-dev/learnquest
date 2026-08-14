<x-lq.glass-card animate>

    <x-lq.section-title>

        🧠 Your Super Skills

    </x-lq.section-title>

    <div class="mt-5 space-y-5">

        @forelse($skills as $skill)

        <div>

            <div class="flex justify-between">

                <div class="flex items-center gap-2">

                    @if($skill['icon'])

                    <x-dynamic-component
                        :component="$skill['icon']"
                        class="h-6 w-6 text-indigo-500" />

                    @else

                    <span class="text-2xl">
                        ⭐
                    </span>

                    @endif


                    <span class="font-semibold">

                        {{ $skill['name'] }}

                    </span>

                </div>

                <div
                    class="text-sm text-slate-500">

                    {{ $skill['xp'] }} XP

                </div>

            </div>

            <div class="mt-2">

                <x-lq.progress-bar
                    :value="$skill['progress']"
                    :max="100"
                    :percentage="$skill['progress']" />

            </div>

        </div>

        @empty

        <p class="text-slate-500">

            Complete activities to build your first skills.

        </p>

        @endforelse

    </div>

</x-lq.glass-card>