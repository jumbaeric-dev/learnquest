<x-child.layout.app-shell
    theme="sky"
    :explorer="[
        'name' => $profile['name'],
        'level' => $profile['level'],
        'xp' => $profile['xp'],
        'streak' => $profile['streak'],
    ]">
    <x-child.layout.section-stack spacing="relaxed">
        @if (session()->has('profile_saved'))
        <div
            role="status"
            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            ✅ {{ session('profile_saved') }}
        </div>
        @endif
        {{-- Page heading --}}
        <section class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-sky-600">
                    Your journey so far
                </p>

                <h1 class="mt-1 text-3xl font-extrabold tracking-tight">
                    My Explorer Profile
                </h1>

                <p class="mt-2 text-sm opacity-75">
                    Your explorer identity and learning achievements, all in one place.
                </p>
            </div>

            <a
                href="{{ url('/child/profile/edit') }}"
                class="inline-flex min-h-11 items-center justify-center gap-2 self-start rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 sm:self-auto">
                <span aria-hidden="true">✏️</span>
                <span>Edit Profile</span>
                <span aria-hidden="true">→</span>
            </a>
        </section>

        {{-- Explorer identity --}}
        <section
            class="lq-glass-card overflow-hidden rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                {{-- Avatar --}}
                <div class="flex shrink-0 justify-center sm:justify-start">
                    <div
                        class="lq-avatar lq-avatar--md flex items-center justify-center"
                        aria-hidden="true">
                        @if (! empty($profile['avatar']))
                        {{ $profile['avatar'] }}
                        @else
                        {{ strtoupper(substr($profile['name'], 0, 1)) }}
                        @endif
                    </div>
                </div>

                {{-- Identity --}}
                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="inline-flex items-center gap-2 rounded-full bg-sky-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-sky-800">
                            🚀 LearnQuest Explorer
                        </span>
                    </div>

                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">
                        {{ $profile['name'] }}
                    </h2>

                    @if (! empty($profile['username']))
                    <p class="mt-1 text-sm font-medium opacity-70">
                        {{ '@' . $profile['username'] }}
                    </p>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                        <span class="inline-flex items-center gap-2 rounded-xl border border-sky-100 bg-white/70 px-3 py-2 text-sm font-semibold">
                            ⭐ Level {{ $profile['level'] }} Explorer
                        </span>

                        @if (! is_null($profile['age']))
                        <span class="inline-flex items-center gap-2 rounded-xl border border-sky-100 bg-white/70 px-3 py-2 text-sm font-semibold">
                            🎂 {{ $profile['age'] }} years old
                        </span>
                        @endif
                    </div>
                </div>

                {{-- XP summary --}}
                <div class="rounded-2xl border border-sky-100 bg-sky-50/80 px-5 py-4 text-center sm:min-w-36">
                    <p class="text-xs font-bold uppercase tracking-wider text-sky-700">
                        Total XP
                    </p>

                    <p class="mt-1 text-3xl font-extrabold text-sky-800">
                        {{ number_format($profile['xp']) }}
                    </p>

                    <p class="mt-1 text-xs text-sky-700">
                        Keep exploring!
                    </p>
                </div>

            </div>
        </section>

        {{-- Learning journey --}}
        <section>
            <div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight sm:text-2xl">
                        My Learning Journey
                    </h2>

                    <p class="mt-1 text-sm opacity-70">
                        Every activity is another step forward.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                {{-- XP --}}
                <article class="lq-glass-card rounded-2xl border border-sky-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium opacity-75">
                                Total Experience
                            </p>

                            <p class="mt-3 text-3xl font-extrabold tracking-tight">
                                {{ number_format($profile['xp']) }}
                            </p>

                            <p class="mt-2 text-xs opacity-65">
                                Experience points earned
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-2xl" aria-hidden="true">
                            ⚡
                        </span>
                    </div>
                </article>

                {{-- Activities --}}
                <article class="lq-glass-card rounded-2xl border border-emerald-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium opacity-75">
                                Activities Completed
                            </p>

                            <p class="mt-3 text-3xl font-extrabold tracking-tight">
                                {{ number_format($profile['completed_activities']) }}
                            </p>

                            <p class="mt-2 text-xs opacity-65">
                                Learning wins achieved
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-100 text-2xl" aria-hidden="true">
                            ✅
                        </span>
                    </div>
                </article>

                {{-- Streak --}}
                <article class="lq-glass-card rounded-2xl border border-orange-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium opacity-75">
                                Current Streak
                            </p>

                            <p class="mt-3 text-3xl font-extrabold tracking-tight">
                                {{ number_format($profile['streak']) }}
                            </p>

                            <p class="mt-2 text-xs opacity-65">
                                Keep your learning going
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-2xl" aria-hidden="true">
                            🔥
                        </span>
                    </div>
                </article>

                {{-- Badges --}}
                <article class="lq-glass-card rounded-2xl border border-amber-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium opacity-75">
                                Badges Earned
                            </p>

                            <p class="mt-3 text-3xl font-extrabold tracking-tight">
                                {{ number_format($profile['badges_count']) }}
                            </p>

                            <p class="mt-2 text-xs opacity-65">
                                Milestones celebrated
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-2xl" aria-hidden="true">
                            🏆
                        </span>
                    </div>
                </article>

                {{-- Skills --}}
                <article class="lq-glass-card rounded-2xl border border-violet-100 p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium opacity-75">
                                Skills in Progress
                            </p>

                            <p class="mt-3 text-3xl font-extrabold tracking-tight">
                                {{ number_format($profile['skills_count']) }}
                            </p>

                            <p class="mt-2 text-xs opacity-65">
                                Skills you're developing
                            </p>
                        </div>

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-2xl" aria-hidden="true">
                            🧠
                        </span>
                    </div>
                </article>

            </div>
        </section>

        {{-- Next adventure --}}
        <section class="lq-glass-card rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-3xl" aria-hidden="true">
                        🌎
                    </span>

                    <div>
                        <h2 class="text-lg font-extrabold">
                            Ready for your next adventure?
                        </h2>

                        <p class="mt-1 text-sm opacity-75">
                            Discover new worlds, build skills and earn more XP.
                        </p>
                    </div>
                </div>

                <a
                    href="{{ route('child.explore') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-sky-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    Explore Learning
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </section>

        {{-- Back navigation --}}
        <div>
            <a
                href="{{ route('child.dashboard') }}"
                class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold opacity-75 transition hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <span aria-hidden="true">←</span>
                Back to My Universe
            </a>
        </div>

    </x-child.layout.section-stack>
</x-child.layout.app-shell>