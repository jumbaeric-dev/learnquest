<div class="space-y-4">

    @php
        $claimedCount = collect($challenges)->where('claimed', true)->count();
    @endphp

    <x-lq.section-title subtitle="Finish them to earn bonus XP. Resets every day!">
        🎯 Today's Challenges
    </x-lq.section-title>

    @if (! empty($challenges))

        <p class="text-sm font-semibold text-slate-500">
            {{ $claimedCount }} of {{ count($challenges) }} claimed today
        </p>

        <div class="space-y-3">

            @foreach ($challenges as $challenge)

                <x-lq.glass-card wire:key="challenge-{{ $challenge['key'] }}">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        <div class="flex items-center gap-4">

                            <div class="text-3xl">
                                <x-lq.world-icon :name="$challenge['icon'] ?? null" class="h-8 w-8" fallback="🎯" />
                            </div>

                            <div>
                                <h3 class="font-bold text-slate-800">
                                    {{ $challenge['title'] }}
                                </h3>

                                @if ($challenge['description'])
                                    <p class="text-sm text-slate-500">
                                        {{ $challenge['description'] }}
                                    </p>
                                @endif

                                <p class="text-sm font-semibold text-indigo-600">
                                    +{{ $challenge['xp'] }} XP
                                </p>
                            </div>

                        </div>


                        <div class="shrink-0">

                            @if ($challenge['claimed'])

                                <x-lq.badge variant="success">
                                    ✓ Claimed
                                </x-lq.badge>

                            @elseif ($challenge['completed'])

                                <x-lq.button
                                    type="button"
                                    wire:click="claim('{{ $challenge['key'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="claim"
                                    variant="primary"
                                    size="sm"
                                >
                                    🎁 Claim +{{ $challenge['xp'] }} XP
                                </x-lq.button>

                            @elseif ($challenge['key'] === 'ask_nova')

                                <x-lq.button
                                    type="button"
                                    wire:click="$dispatch('open-nova-chat')"
                                    variant="secondary"
                                    size="sm"
                                >
                                    🤖 Ask Nova
                                </x-lq.button>

                            @else

                                <x-lq.button
                                    tag="a"
                                    href="{{ route('child.explore') }}"
                                    variant="secondary"
                                    size="sm"
                                >
                                    🚀 Let's go
                                </x-lq.button>

                            @endif

                        </div>

                    </div>

                </x-lq.glass-card>

            @endforeach

        </div>

    @endif

</div>