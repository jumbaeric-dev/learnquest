<x-parent-portal.layout.app-shell active="overview">
    <div class="space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('parent.dashboard') }}" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">← Family overview</a>
                <h1 class="mt-3 text-3xl font-extrabold">{{ $report['child']['name'] }}</h1>
                <p class="mt-1 text-sm text-slate-400">Learning progress and activity summary</p>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-right">
                <p class="text-xs text-slate-400">Explorer level</p>
                <p class="text-xl font-bold">Level {{ $report['child']['level'] }}</p>
                <p class="text-sm text-cyan-300">{{ number_format($report['child']['xp']) }} XP</p>
            </div>
        </div>

        <section class="grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="Learning summary">
            <x-lq.stat-chip icon="🔥" label="Current streak" :value="$report['streak']['current']" />
            <x-lq.stat-chip icon="🏆" label="Longest streak" :value="$report['streak']['longest']" />
            <x-lq.stat-chip icon="🏅" label="Badges earned" :value="count($report['badges'])" />
            <x-lq.stat-chip icon="🤖" label="Nova prompts this week" :value="$report['nova']['messages_this_week']" />
        </section>

        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-bold text-slate-300">Course progress</h2>
                <p class="text-sm text-slate-400">Progress recorded by LearnQuest's course progression system.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                @forelse ($report['courses'] as $course)
                <x-lq.glass-card class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-semibold !text-slate-900">{{ $course['title'] }}</h3>
                            <p class="text-xs text-slate-400">{{ $course['subject'] ?? 'Learning' }}</p>
                        </div>
                        <span class="text-sm font-bold text-cyan-300">{{ round($course['percentage']) }}%</span>
                    </div>
                    <x-lq.progress-bar :value="$course['percentage']" :max="100" color="cyan" :show-label="false" />
                    <p class="text-xs text-slate-500">{{ $course['completed'] ? 'Completed' : 'In progress' }}@if($course['updated_at']) · Updated {{ $course['updated_at'] }}@endif</p>
                </x-lq.glass-card>
                @empty
                <p class="text-sm text-slate-400">No course progress recorded yet.</p>
                @endforelse
            </div>
        </section>

        @if ($canSeeSkillReports)
        <section class="grid gap-6 lg:grid-cols-2">
            <x-lq.glass-card class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold !text-slate-900">Strengths</h2>
                    <p class="text-sm text-slate-400">Skills with the highest recorded progress.</p>
                </div>
                @forelse ($report['skills']['strongest'] as $skill)
                <div class="space-y-1">
                    <div class="flex justify-between gap-3 text-sm !text-slate-900"><span>{{ $skill['name'] }}</span>
                        <span class="text-slate-400">{{ round($skill['progress']) }}%</span>
                    </div>
                    <x-lq.progress-bar :value="$skill['progress']" :max="100" color="cyan" :show-label="false" />
                </div>
                @empty
                <p class="text-sm text-slate-400">Skill progress will appear as activities are completed.</p>
                @endforelse
            </x-lq.glass-card>

            <x-lq.glass-card class="space-y-4">
                <div>
                    <h2 class="text-xl font-bold !text-slate-900">Growth opportunities</h2>
                    <p class="text-sm text-slate-400">Skills with the lowest recorded progress, not a judgement of ability.</p>
                </div>
                @forelse ($report['skills']['growth_areas'] as $skill)
                <div class="space-y-1">
                    <div class="flex justify-between gap-3 text-sm !text-slate-900">
                        <span>{{ $skill['name'] }}</span>
                        <span class="text-slate-400">{{ round($skill['progress']) }}%</span>
                    </div>
                    <x-lq.progress-bar :value="$skill['progress']" :max="100" color="purple" :show-label="false" />
                </div>
                @empty
                <p class="text-sm text-slate-400">No skill gaps to show yet.</p>
                @endforelse
            </x-lq.glass-card>
        </section>
        @else
        <section class="rounded-2xl border border-white/10 bg-white p-6 text-slate-900 shadow-lg">
            <h2 class="text-xl font-bold">Skill reports are part of Premium</h2>
            <p class="mt-1 text-sm text-slate-600">See {{ $report['child']['name'] }}’s strongest skills and where they can grow.</p>
            <a href="{{ route('parent.billing') }}" class="mt-4 inline-flex rounded-lg bg-cyan-700 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-800">See plans</a>
        </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-2">
            <x-lq.glass-card class="space-y-4">
                <h2 class="text-xl font-bold !text-slate-900">Recent learning activity</h2>
                @forelse ($report['recent_activity'] as $activity)
                <div class="flex items-start justify-between gap-3 border-b border-white/10 pb-3 last:border-0 !text-slate-900">
                    <div>
                        <p class="font-medium">{{ $activity['title'] }}</p>
                        <p class="text-xs text-slate-400">{{ $activity['course'] ?? 'Learning activity' }} · {{ $activity['completed_at'] ?? 'Date unavailable' }}</p>
                    </div>
                    <span class="shrink-0 text-sm text-cyan-300">+{{ $activity['xp'] }} XP</span>
                </div>
                @empty
                <p class="text-sm text-slate-400">Completed activities will appear here.</p>
                @endforelse
            </x-lq.glass-card>

            <x-lq.glass-card class="space-y-4">
                <h2 class="text-xl font-bold !text-slate-900">Achievements</h2>
                @forelse ($report['badges'] as $badge)
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-xl">{{ $badge['icon'] ?: '🏅' }}</span>
                    <div class="min-w-0">
                        <p class="font-semibold !text-slate-900">{{ $badge['name'] }}</p>
                        <p class="text-xs text-slate-400">{{ $badge['description'] }}</p>
                        @if($badge['earned_at'])<p class="text-xs text-slate-500">Earned {{ $badge['earned_at'] }}</p>@endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400">New badges will appear here when earned.</p>
                @endforelse
            </x-lq.glass-card>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <x-lq.glass-card class="space-y-3">
                <h2 class="text-xl font-bold !text-slate-900">Daily challenge activity</h2>
                @forelse ($report['daily_challenges'] as $challenge)
                <div class="flex justify-between gap-3 text-sm"><span>{{ $challenge['name'] }} <span class="text-slate-500">· {{ $challenge['date'] }}</span></span><span class="text-cyan-300">+{{ $challenge['xp'] }} XP</span></div>
                @empty
                <p class="text-sm text-slate-400">Claimed daily challenges will appear here.</p>
                @endforelse
            </x-lq.glass-card>

            <x-lq.glass-card class="space-y-3">
                <h2 class="text-xl font-bold !text-slate-900">Learning consistency</h2>
                <p class="text-sm text-slate-400">Days with at least one completed activity in the last 14 days.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($report['streak']['active_dates'] as $date)
                    <span class="rounded-lg bg-cyan-400/10 px-2 py-1 text-xs text-cyan-200">{{ \Carbon\Carbon::parse($date)->format('M j') }}</span>
                    @endforeach
                    @if(empty($report['streak']['active_dates']))<span class="text-sm text-slate-400">No recent activity recorded.</span>@endif
                </div>
                @if($report['streak']['last_activity_date'])
                <p class="text-xs text-slate-500">Last streak activity: {{ $report['streak']['last_activity_date'] }}</p>
                @endif
            </x-lq.glass-card>
        </section>
    </div>
</x-parent-portal.layout.app-shell>