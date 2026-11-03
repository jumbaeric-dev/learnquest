<x-parent-portal.layout.app-shell active="overview">
    <div class="space-y-8">

        <div>
            <h1 class="text-3xl font-extrabold">
                Welcome back, {{ Str::before(auth()->user()->name, ' ') }}
            </h1>

            <p class="mt-1 text-sm text-slate-400">
                How your explorers did over the last {{ $overview['window_days'] }} days.
            </p>
        </div>

        @if (empty($overview['children']))

        <x-lq.empty-state
            icon="🧑‍🚀"
            title="No explorers yet"
            message="Your children's profiles will appear here once they've been added." />

        @else

        {{-- Family summary --}}
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
            <x-lq.stat-chip
                icon="🧑‍🚀"
                label="Explorers"
                :value="$overview['family']['children']" />

            <x-lq.stat-chip
                icon="⚡"
                label="Active this week"
                :value="$overview['family']['active_children']"
                variant="success" />

            <x-lq.stat-chip
                icon="✅"
                label="Activities done"
                :value="$overview['family']['activities']"
                variant="primary" />

            <x-lq.stat-chip
                icon="📘"
                label="Lessons done"
                :value="$overview['family']['lessons']"
                variant="primary" />

            <x-lq.stat-chip
                icon="🌟"
                label="Family XP"
                :value="number_format($overview['family']['xp'])"
                variant="reward" />

            <x-lq.stat-chip
                icon="📅"
                label="Family active days"
                :value="$overview['family']['active_days'] . '/' . $overview['window_days']" />

            <x-lq.stat-chip
                icon="🏅"
                label="Badges earned"
                :value="$overview['family']['badges']"
                variant="reward" />
        </div>

        {{-- One card per child --}}
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($overview['children'] as $child)
            <x-lq.glass-card class="space-y-5" wire:key="child-{{ $child['id'] }}">

                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-purple-600 text-lg font-extrabold text-white">
                            {{ Str::upper(Str::substr($child['name'], 0, 1)) }}
                        </div>

                        <div>
                            <h3 class="text-lg font-bold !text-slate-900">
                                {{ $child['name'] }}
                            </h3>

                            <p class="text-xs font-medium text-slate-600">
                                Level {{ $child['level'] }} · {{ number_format($child['xp']) }} XP
                            </p>
                        </div>
                    </div>

                    @if (! $child['is_active'])
                    <x-lq.badge variant="locked">Paused</x-lq.badge>
                    @elseif ($child['week']['activities'] > 0)
                    <x-lq.badge variant="success">Active this week</x-lq.badge>
                    @else
                    <x-lq.badge variant="neutral">Quiet week</x-lq.badge>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <x-lq.stat-chip
                        icon="🔥"
                        label="Day streak"
                        :value="$child['streak']['current']"
                        size="sm" />

                    <x-lq.stat-chip
                        icon="✅"
                        label="Activities"
                        :value="$child['week']['activities']"
                        size="sm" />

                    <x-lq.stat-chip
                        icon="📘"
                        label="Lessons"
                        :value="$child['week']['lessons']"
                        size="sm" />

                    <x-lq.stat-chip
                        icon="📅"
                        label="Active days"
                        :value="$child['week']['active_days'] . '/' . $overview['window_days']"
                        size="sm" />
                </div>

                <div class="space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Currently learning
                    </h4>

                    @forelse ($child['courses'] as $course)
                    <div>
                        <div class="mb-1 flex items-baseline justify-between gap-2 text-sm">
                            <span class="truncate">
                                {{ $course['title'] }}
                                @if ($course['subject'])
                                <span class="text-xs text-slate-500">· {{ $course['subject'] }}</span>
                                @endif
                            </span>

                            <span class="shrink-0 text-slate-400">
                                {{ round($course['percentage']) }}%
                            </span>
                        </div>

                        <x-lq.progress-bar
                            :value="$course['percentage']"
                            :max="100"
                            color="cyan"
                            :show-label="false" />
                    </div>
                    @empty
                    <p class="text-sm text-slate-500">No courses in progress yet.</p>
                    @endforelse
                </div>

                <div class="flex items-center justify-between gap-3">
                    <p class="text-xs font-medium text-slate-600">
                        Last active: {{ $child['last_active_label'] }}
                    </p>

                    <a
                        href="{{ route('parent.children.show', $child['id']) }}"
                        class="inline-flex items-center gap-1 rounded-lg bg-cyan-50 px-3 py-2 text-sm font-bold !text-cyan-800 transition hover:bg-cyan-100 hover:!text-cyan-950">
                        View details
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </x-lq.glass-card>
            @endforeach
        </div>

        @endif
    </div>
</x-parent-portal.layout.app-shell>