@php
    $explorer = $dashboard['explorer'];
    $welcome = $dashboard['welcome'];
    $nova = $dashboard['nova'];
    $mission = $dashboard['mission'];
    $learning = $dashboard['learning'];
    $skills = $dashboard['skills'];
    $achievements = $dashboard['achievements'];
    $worlds = $dashboard['worlds'];
    $recommendations = $dashboard['recommendations'];
    $challenge = $dashboard['dailyChallenge'];

    $initials = strtoupper(substr($explorer['name'] ?? 'E', 0, 1));

    $xpPercentage = max(
        0,
        min(100, (int) ($explorer['xpPercentage'] ?? 0))
    );
@endphp

<div
    class="lq-universe lq-universe-shell"
    x-data="{
        profileOpen: false,
        logoutOpen: false,

        openProfile() {
            this.logoutOpen = false;
            this.profileOpen = true;
        },

        closeProfile() {
            this.profileOpen = false;
        },

        openLogout() {
            this.profileOpen = false;
            this.logoutOpen = true;
        },

        closeLogout() {
            this.logoutOpen = false;
        }
    }"
    @keydown.escape.window="
        if (logoutOpen) {
            closeLogout();
        } else if (profileOpen) {
            closeProfile();
        }
    "
>

    {{-- ============================================================
         DESKTOP SIDEBAR
         ============================================================ --}}

    <aside
        class="lq-sidebar"
        aria-label="LearnQuest navigation"
    >

        {{-- Brand --}}
        <div class="lq-brand">

            <div
                class="lq-brand-mark"
                aria-hidden="true"
            >
                🚀
            </div>

            <div class="lq-brand-name">
                Learn<span>Quest</span>
            </div>

        </div>


        {{-- Main navigation --}}
        <nav class="lq-sidebar-nav">

            <a
                href="{{ route('child.dashboard') }}"
                class="lq-nav-link is-active"
            >
                <span
                    class="lq-nav-icon"
                    aria-hidden="true"
                >
                    🌌
                </span>

                <span>
                    My Universe
                </span>
            </a>


            <a
                href="{{ route('child.explore') }}"
                class="lq-nav-link"
            >
                <span
                    class="lq-nav-icon"
                    aria-hidden="true"
                >
                    🧭
                </span>

                <span>
                    Explorer
                </span>
            </a>


            <a
                href="{{ route('child.missions') }}"
                class="lq-nav-link"
            >
                <span
                    class="lq-nav-icon"
                    aria-hidden="true"
                >
                    🌍
                </span>

                <span>
                    Worlds
                </span>
            </a>


            <a
                href="{{ route('child.missions') }}"
                class="lq-nav-link"
            >
                <span
                    class="lq-nav-icon"
                    aria-hidden="true"
                >
                    📚
                </span>

                <span>
                    Courses
                </span>
            </a>

        </nav>


        {{-- Nova shortcut --}}
        <div class="lq-sidebar-bottom">

            <div class="lq-card lq-card-pad">

                <div class="lq-row">

                    <div class="lq-nova-avatar">
                        🤖
                    </div>

                    <div class="lq-grow">

                        <div class="lq-card-title">
                            Nova
                        </div>

                        <div class="lq-card-meta">
                            Ready to help
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </aside>


    {{-- ============================================================
         MAIN CONTENT
         ============================================================ --}}

    <main class="lq-main">

        <div class="lq-page">


            {{-- ====================================================
                 RESPONSIVE TOP BAR
                 ==================================================== --}}

            <header class="lq-topbar">

                {{-- Mobile brand --}}
                <div class="lq-mobile-brand">

                    <div
                        class="lq-brand-mark"
                        aria-hidden="true"
                    >
                        🚀
                    </div>

                    <span>
                        Learn<span style="color:#f59e0b">Quest</span>
                    </span>

                </div>


                {{-- Top-right actions --}}
                <div class="lq-top-actions">


                    {{-- Notifications --}}
                    <button
                        type="button"
                        class="lq-icon-button"
                        aria-label="Notifications"
                    >
                        🔔
                    </button>


                    {{-- =================================================
                         PROFILE BUTTON + DROPDOWN
                         ================================================= --}}

                    <div
                        class="relative"
                        @click.outside="closeProfile()"
                    >

                        {{-- Avatar --}}
                        <button
                            type="button"
                            class="lq-avatar cursor-pointer border-0"
                            aria-label="Open profile menu"
                            aria-haspopup="menu"
                            :aria-expanded="profileOpen.toString()"
                            @click="
                                profileOpen
                                    ? closeProfile()
                                    : openProfile()
                            "
                        >
                            {{ $initials }}
                        </button>


                        {{-- =================================================
                             PROFILE DROPDOWN
                             ================================================= --}}

                        <div
                            x-cloak
                            x-show="profileOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-12 z-[100] w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
                            role="menu"
                            aria-label="Profile menu"
                        >

                            {{-- Profile summary --}}
                            <div class="border-b border-slate-100 bg-slate-50 px-4 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="lq-avatar shrink-0">
                                        {{ $initials }}
                                    </div>

                                    <div class="min-w-0">

                                        <div class="truncate text-sm font-black text-slate-900">
                                            {{ $explorer['name'] }}
                                        </div>

                                        <div class="mt-0.5 text-xs font-semibold text-slate-500">
                                            Level {{ $explorer['level'] }} Explorer
                                        </div>

                                    </div>

                                </div>


                                {{-- Mini progress stats --}}
                                <div class="mt-4 grid grid-cols-2 gap-2">

                                    <div class="rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-100">

                                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            XP
                                        </div>

                                        <div class="mt-0.5 text-sm font-black text-slate-900">
                                            {{ number_format($explorer['xp']) }}
                                        </div>

                                    </div>


                                    <div class="rounded-xl bg-white px-3 py-2 shadow-sm ring-1 ring-slate-100">

                                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                                            Streak
                                        </div>

                                        <div class="mt-0.5 text-sm font-black text-slate-900">
                                            🔥 {{ $explorer['streak'] }}
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Menu items --}}
                            <div class="p-2">

                                {{-- My Profile --}}
                                <a
                                    href="#"
                                    role="menuitem"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                                    @click="closeProfile()"
                                >
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-lg"
                                        aria-hidden="true"
                                    >
                                        👤
                                    </span>

                                    <span>
                                        My Profile
                                    </span>
                                </a>


                                {{-- Achievements --}}
                                <a
                                    href="#"
                                    role="menuitem"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-amber-50 hover:text-amber-700"
                                    @click="closeProfile()"
                                >
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-lg"
                                        aria-hidden="true"
                                    >
                                        🏆
                                    </span>

                                    <span>
                                        My Achievements
                                    </span>
                                </a>


                                {{-- Settings --}}
                                <a
                                    href="#"
                                    role="menuitem"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-100 hover:text-slate-900"
                                    @click="closeProfile()"
                                >
                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-lg"
                                        aria-hidden="true"
                                    >
                                        ⚙️
                                    </span>

                                    <span>
                                        Settings
                                    </span>
                                </a>

                            </div>


                            {{-- Logout --}}
                            <div class="border-t border-slate-100 p-2">

                                <button
                                    type="button"
                                    role="menuitem"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-black text-red-600 transition hover:bg-red-50"
                                    @click="openLogout()"
                                >

                                    <span
                                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-50 text-lg"
                                        aria-hidden="true"
                                    >
                                        🚪
                                    </span>

                                    <span>
                                        Log out
                                    </span>

                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </header>


            {{-- ====================================================
                 HERO
                 ==================================================== --}}

            <section
                class="lq-hero"
                aria-labelledby="universe-heading"
            >

                <div class="lq-hero-content">

                    <div class="lq-hero-kicker">

                        <span>
                            🌟
                        </span>

                        <span>
                            Level {{ $explorer['level'] }} Explorer
                        </span>

                    </div>


                    <h1 id="universe-heading">
                        {{ $welcome['greeting'] }}
                    </h1>


                    <p>
                        {{ $welcome['headline'] }}
                        {{ $welcome['message'] }}
                    </p>


                    <div class="lq-hero-actions">

                        @if($mission)

                            <a
                                href="#current-mission"
                                class="lq-button-primary"
                            >
                                🚀 {{ $mission['buttonText'] }} Quest
                            </a>

                        @else

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-button-primary"
                            >
                                🧭 Start Adventure
                            </a>

                        @endif


                        <a
                            href="#continue-learning"
                            class="lq-button-secondary"
                        >
                            View Progress
                        </a>

                    </div>

                </div>

            </section>


            {{-- ====================================================
                 QUICK STATS
                 ==================================================== --}}

            <section
                class="lq-stat-grid"
                aria-label="Your progress"
            >

                {{-- XP --}}
                <article class="lq-stat-card">

                    <div class="lq-stat-card-top">

                        <span>
                            ⭐ XP
                        </span>

                        <span>
                            Growth
                        </span>

                    </div>

                    <div class="lq-stat-card-value">
                        {{ number_format($explorer['xp']) }}
                    </div>

                </article>


                {{-- Streak --}}
                <article class="lq-stat-card">

                    <div class="lq-stat-card-top">

                        <span>
                            🔥 Streak
                        </span>

                        <span>
                            Days
                        </span>

                    </div>

                    <div class="lq-stat-card-value">
                        {{ $explorer['streak'] }}
                    </div>

                </article>


                {{-- Level --}}
                <article class="lq-stat-card">

                    <div class="lq-stat-card-top">

                        <span>
                            🚀 Level
                        </span>

                        <span>
                            Explorer
                        </span>

                    </div>

                    <div class="lq-stat-card-value">
                        {{ $explorer['level'] }}
                    </div>

                </article>


                {{-- Future readiness --}}
                <article class="lq-stat-card">

                    <div class="lq-stat-card-top">

                        <span>
                            🧭 Readiness
                        </span>

                        <span>
                            Future
                        </span>

                    </div>

                    <div class="lq-stat-card-value">
                        {{ $explorer['futureReadiness'] }}%
                    </div>

                </article>

            </section>


            {{-- ====================================================
                 DASHBOARD GRID
                 ==================================================== --}}

            <div class="lq-dashboard-grid">


                {{-- =================================================
                     MAIN COLUMN
                     ================================================= --}}

                <div class="lq-main-column">


                    {{-- =================================================
                         CONTINUE LEARNING
                         ================================================= --}}

                    <section
                        id="continue-learning"
                        class="lq-section"
                    >

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Continue Learning
                                </h2>

                                <p class="lq-section-subtitle">
                                    Pick up where you left off.
                                </p>

                            </div>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                Explore
                            </a>

                        </div>


                        <article class="lq-card lq-card-pad">

                            @if($learning)

                                <div class="lq-continue">

                                    <div class="lq-row">

                                        <div class="lq-course-icon">
                                            📚
                                        </div>

                                        <div class="lq-grow">

                                            <h3 class="lq-card-title">
                                                {{ $learning['course'] }}
                                            </h3>

                                            <p class="lq-card-meta">
                                                {{ $learning['lesson'] }}
                                            </p>

                                        </div>

                                        <span class="lq-card-meta">
                                            {{ $learning['progress'] }}%
                                        </span>

                                    </div>


                                    <div>

                                        <div class="lq-progress-line">

                                            <span>
                                                Course progress
                                            </span>

                                            <strong>
                                                {{ $learning['progress'] }}%
                                            </strong>

                                        </div>


                                        <div
                                            class="lq-progress"
                                            role="progressbar"
                                            aria-valuenow="{{ $learning['progress'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                        >

                                            <span
                                                style="width: {{ $learning['progress'] }}%"
                                            ></span>

                                        </div>

                                    </div>


                                    <div>

                                        <a
                                            href="{{ route('child.explore') }}"
                                            class="lq-action"
                                        >
                                            Continue Learning →
                                        </a>

                                    </div>

                                </div>

                            @else

                                <div class="lq-empty">
                                    Start your first course to begin your learning journey.
                                </div>

                            @endif

                        </article>

                    </section>


                    {{-- =================================================
                         SKILLS
                         ================================================= --}}

                    <section class="lq-section">

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Your Super Skills
                                </h2>

                                <p class="lq-section-subtitle">
                                    Capabilities you're building.
                                </p>

                            </div>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                View all
                            </a>

                        </div>


                        <div class="lq-skill-grid">

                            @forelse($skills as $skill)

                                <article class="lq-card lq-skill-card">

                                    <div class="lq-skill-top">

                                        <div class="lq-skill-icon">

                                            @if($skill['icon'])

                                                <x-dynamic-component
                                                    :component="$skill['icon']"
                                                    class="h-5 w-5 text-blue-600"
                                                />

                                            @else

                                                🧠

                                            @endif

                                        </div>


                                        <span class="lq-skill-xp">
                                            {{ $skill['xp'] }} XP
                                        </span>

                                    </div>


                                    <div class="lq-skill-name">
                                        {{ $skill['name'] }}
                                    </div>


                                    <div class="mt-2">

                                        <div class="lq-progress">

                                            <span
                                                style="width: {{ max(0, min(100, (int) $skill['progress'])) }}%"
                                            ></span>

                                        </div>

                                    </div>

                                </article>

                            @empty

                                <div
                                    class="lq-card lq-empty"
                                    style="grid-column: 1 / -1;"
                                >
                                    Complete activities to build your first skills.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- =================================================
                         LEARNING WORLDS
                         ================================================= --}}

                    <section class="lq-section">

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Learning Worlds
                                </h2>

                                <p class="lq-section-subtitle">
                                    Choose a world and start your adventure.
                                </p>

                            </div>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                Explore
                            </a>

                        </div>


                        <div class="lq-world-grid">

                            @forelse($worlds as $index => $world)

                                <a
                                    href="{{ route('child.missions') }}"
                                    class="lq-world-card"
                                >

                                    <div class="lq-world-icon">
                                        {{ $world['icon'] ?? '🌎' }}
                                    </div>

                                    <div class="lq-world-name">
                                        {{ $world['name'] }}
                                    </div>

                                    <div class="lq-world-meta">
                                        {{ $world['courses'] }} courses
                                    </div>

                                    <div class="lq-world-progress">
                                        <span></span>
                                    </div>

                                </a>

                            @empty

                                <div
                                    class="lq-card lq-empty"
                                    style="grid-column: 1 / -1;"
                                >
                                    New learning worlds are coming soon.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- =================================================
                         RECOMMENDED ADVENTURES
                         ================================================= --}}

                    <section class="lq-section">

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Recommended Adventures
                                </h2>

                                <p class="lq-section-subtitle">
                                    A few quests picked for you.
                                </p>

                            </div>

                        </div>


                        <div class="lq-adventure-grid">

                            @forelse($recommendations as $adventure)

                                <article class="lq-card lq-adventure-card">

                                    <div class="lq-row">

                                        <div class="lq-adventure-icon">

                                            @if($adventure['icon'])

                                                <x-dynamic-component
                                                    :component="$adventure['icon']"
                                                    class="h-5 w-5 text-orange-500"
                                                />

                                            @else

                                                🚀

                                            @endif

                                        </div>


                                        <div class="lq-grow">

                                            <h3 class="lq-card-title">
                                                {{ $adventure['title'] }}
                                            </h3>

                                            <p class="lq-card-meta">
                                                Ages {{ $adventure['ageGroup'] }}
                                            </p>

                                        </div>

                                    </div>


                                    <p class="lq-card-meta mt-3">
                                        {{ $adventure['description'] }}
                                    </p>


                                    <div class="mt-3">

                                        <a
                                            href="{{ route('child.explore') }}"
                                            class="lq-action"
                                        >
                                            Explore →
                                        </a>

                                    </div>

                                </article>

                            @empty

                                <div
                                    class="lq-card lq-empty"
                                    style="grid-column: 1 / -1;"
                                >
                                    More adventures are coming soon.
                                </div>

                            @endforelse

                        </div>

                    </section>

                </div>


                {{-- =================================================
                     SIDE COLUMN
                     ================================================= --}}

                <aside class="lq-side-column">


                    {{-- =================================================
                         NOVA
                         ================================================= --}}

                    <section class="lq-section">

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Nova the Explorer
                                </h2>

                                <p class="lq-section-subtitle">
                                    Your learning companion.
                                </p>

                            </div>

                        </div>


                        <article class="lq-nova">

                            <div class="lq-nova-avatar">
                                🤖
                            </div>


                            <div class="lq-grow">

                                <h3 class="lq-card-title">
                                    {{ $nova['greeting'] }}
                                </h3>

                                <p class="lq-card-meta">
                                    {{ $nova['message'] }}
                                </p>

                                <button
                                    type="button"
                                    class="lq-action mt-3"
                                >
                                    {{ $nova['buttonText'] }} →
                                </button>

                            </div>

                        </article>

                    </section>


                    {{-- =================================================
                         CURRENT QUEST
                         ================================================= --}}

                    <section
                        id="current-mission"
                        class="lq-section"
                    >

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Current Quest
                                </h2>

                                <p class="lq-section-subtitle">
                                    Your next step.
                                </p>

                            </div>

                        </div>


                        <article class="lq-card lq-card-pad">

                            @if($mission)

                                <div class="lq-row">

                                    <div class="lq-course-icon">
                                        🎯
                                    </div>

                                    <div class="lq-grow">

                                        <h3 class="lq-card-title">
                                            {{ $mission['activity'] }}
                                        </h3>

                                        <p class="lq-card-meta">
                                            {{ $mission['lesson'] }}
                                        </p>

                                    </div>

                                </div>


                                <div class="mt-4">

                                    <div class="lq-progress-line">

                                        <span>
                                            {{ $mission['estimatedMinutes'] }} min
                                            ·
                                            +{{ $mission['xpReward'] }} XP
                                        </span>

                                        <strong>
                                            {{ $mission['progress'] }}%
                                        </strong>

                                    </div>


                                    <div class="lq-progress">

                                        <span
                                            style="width: {{ max(0, min(100, (int) $mission['progress'])) }}%"
                                        ></span>

                                    </div>

                                </div>


                                <a
                                    href="{{ route('child.missions') }}"
                                    class="lq-action mt-4 w-full"
                                >
                                    {{ $mission['buttonText'] }} →
                                </a>

                            @else

                                <div class="lq-empty">

                                    🎉 You're all caught up!

                                    <br>

                                    Come back for your next quest.

                                </div>

                            @endif

                        </article>

                    </section>


                    {{-- =================================================
                         RECENT BADGES
                         ================================================= --}}

                    <section class="lq-section">

                        <div class="lq-section-head">

                            <div>

                                <h2 class="lq-section-title">
                                    Recent Badges
                                </h2>

                                <p class="lq-section-subtitle">
                                    Milestones you've earned.
                                </p>

                            </div>

                        </div>


                        <div class="lq-badge-grid">

                            @forelse($achievements as $achievement)

                                <article class="lq-card lq-badge-card">

                                    <div class="lq-badge-icon">

                                        @if($achievement['icon'])

                                            <x-dynamic-component
                                                :component="$achievement['icon']"
                                                class="h-5 w-5 text-amber-500"
                                            />

                                        @else

                                            🏆

                                        @endif

                                    </div>


                                    <div class="lq-grow">

                                        <div class="lq-badge-name">
                                            {{ $achievement['name'] }}
                                        </div>

                                        <div class="lq-badge-date">
                                            {{ $achievement['earnedAt'] ?? 'Achievement' }}
                                        </div>

                                    </div>

                                </article>

                            @empty

                                <div
                                    class="lq-card lq-empty"
                                    style="grid-column: 1 / -1;"
                                >
                                    Complete activities to unlock your first badges.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- =================================================
                         DAILY CHALLENGE
                         ================================================= --}}

                    <section class="lq-section">

                        <article class="lq-card lq-card-pad lq-challenge">

                            <div class="lq-row">

                                <div class="lq-course-icon">
                                    🎯
                                </div>

                                <div class="lq-grow">

                                    <h3 class="lq-card-title">
                                        {{ $challenge['title'] }}
                                    </h3>

                                    <p class="lq-card-meta">
                                        {{ $challenge['description'] }}
                                    </p>

                                </div>

                            </div>


                            <div class="lq-challenge-meter">

                                <span>
                                    {{ $challenge['current'] }}
                                    /
                                    {{ $challenge['target'] }}
                                    complete
                                </span>

                                <span>
                                    +{{ $challenge['xpReward'] }} XP
                                </span>

                            </div>


                            <div class="lq-progress mt-2">

                                <span
                                    style="width: {{ min(100, ($challenge['current'] / max(1, $challenge['target'])) * 100) }}%"
                                ></span>

                            </div>


                            <button
                                type="button"
                                class="lq-action mt-4 w-full"
                            >
                                {{ $challenge['buttonText'] }}
                            </button>

                        </article>

                    </section>

                </aside>

            </div>

        </div>

    </main>


    {{-- ================================================================
         MOBILE BOTTOM NAVIGATION
         ================================================================ --}}

    <nav
        class="lq-mobile-bottom"
        aria-label="LearnQuest mobile navigation"
    >

        <a
            href="{{ route('child.dashboard') }}"
            class="lq-mobile-nav-item is-active"
        >
            <span>🌌</span>
            <span>My Universe</span>
        </a>


        <a
            href="{{ route('child.explore') }}"
            class="lq-mobile-nav-item"
        >
            <span>🧭</span>
            <span>Explorer</span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-mobile-nav-item"
        >
            <span>🌍</span>
            <span>Worlds</span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-mobile-nav-item"
        >
            <span>📚</span>
            <span>Courses</span>
        </a>


        {{-- Mobile profile --}}
        <button
            type="button"
            class="lq-mobile-nav-item border-0 bg-transparent"
            @click="
                profileOpen
                    ? closeProfile()
                    : openProfile()
            "
            :aria-expanded="profileOpen.toString()"
            aria-haspopup="dialog"
        >
            <span>
                👤
            </span>

            <span>
                Profile
            </span>
        </button>

    </nav>


    {{-- ================================================================
         MOBILE PROFILE SHEET
         ================================================================ --}}

    <div
        x-cloak
        x-show="profileOpen"
        class="fixed inset-0 z-[90] lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Profile menu"
    >

        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-950/40 backdrop-blur-sm"
            @click="closeProfile()"
        ></div>


        {{-- Bottom sheet --}}
        <div
            x-show="profileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="absolute inset-x-0 bottom-0 overflow-hidden rounded-t-3xl bg-white shadow-2xl"
        >

            {{-- Handle --}}
            <div class="flex justify-center pt-3">

                <div class="h-1.5 w-12 rounded-full bg-slate-200"></div>

            </div>


            {{-- Summary --}}
            <div class="px-5 pb-4 pt-4">

                <div class="flex items-center gap-3">

                    <div class="lq-avatar shrink-0">
                        {{ $initials }}
                    </div>

                    <div class="min-w-0">

                        <div class="truncate text-base font-black text-slate-900">
                            {{ $explorer['name'] }}
                        </div>

                        <div class="text-xs font-semibold text-slate-500">
                            Level {{ $explorer['level'] }} Explorer
                        </div>

                    </div>

                </div>


                <div class="mt-4 grid grid-cols-2 gap-2">

                    <div class="rounded-xl bg-blue-50 px-3 py-2">

                        <div class="text-[10px] font-bold uppercase tracking-wide text-blue-500">
                            XP
                        </div>

                        <div class="mt-0.5 text-sm font-black text-blue-900">
                            {{ number_format($explorer['xp']) }}
                        </div>

                    </div>


                    <div class="rounded-xl bg-orange-50 px-3 py-2">

                        <div class="text-[10px] font-bold uppercase tracking-wide text-orange-500">
                            Streak
                        </div>

                        <div class="mt-0.5 text-sm font-black text-orange-900">
                            🔥 {{ $explorer['streak'] }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Mobile menu --}}
            <div class="border-t border-slate-100 px-3 py-2">

                <a
                    href="#"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-blue-50"
                    @click="closeProfile()"
                >

                    <span class="text-lg">
                        👤
                    </span>

                    <span>
                        My Profile
                    </span>

                </a>


                <a
                    href="#"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-amber-50"
                    @click="closeProfile()"
                >

                    <span class="text-lg">
                        🏆
                    </span>

                    <span>
                        My Achievements
                    </span>

                </a>


                <a
                    href="#"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-bold text-slate-700 hover:bg-slate-100"
                    @click="closeProfile()"
                >

                    <span class="text-lg">
                        ⚙️
                    </span>

                    <span>
                        Settings
                    </span>

                </a>


                <button
                    type="button"
                    class="mb-2 mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-black text-red-600 hover:bg-red-50"
                    @click="openLogout()"
                >

                    <span class="text-lg">
                        🚪
                    </span>

                    <span>
                        Log out
                    </span>

                </button>

            </div>

        </div>

    </div>


    {{-- ================================================================
         LOGOUT CONFIRMATION
         ================================================================ --}}

    <div
        x-cloak
        x-show="logoutOpen"
        class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/50 p-5 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logout-title"
    >

        {{-- Confirmation card --}}
        <div
            x-show="logoutOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl"
        >

            {{-- Icon --}}
            <div class="flex justify-center">

                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-50 text-3xl">
                    🚀
                </div>

            </div>


            {{-- Message --}}
            <div class="mt-4 text-center">

                <h2
                    id="logout-title"
                    class="text-xl font-black tracking-tight text-slate-900"
                >
                    Ready to leave your adventure?
                </h2>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Your progress is saved. You can come back anytime.
                </p>

            </div>


            {{-- Actions --}}
            <div class="mt-6 grid grid-cols-2 gap-3">

                <button
                    type="button"
                    class="min-h-11 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    @click="closeLogout()"
                >
                    Stay
                </button>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <button
                        type="submit"
                        class="min-h-11 w-full rounded-xl bg-red-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-red-700"
                    >
                        Log out
                    </button>

                </form>

            </div>

        </div>

    </div>


    {{-- Existing floating actions --}}
    <livewire:child.layouts.components.floating-actions />

</div>