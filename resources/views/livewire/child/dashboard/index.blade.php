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

    $initials = strtoupper(
        substr($explorer['name'] ?? 'E', 0, 1)
    );

    $challengeProgress = min(
        100,
        (
            (float) $challenge['current']
            /
            max(1, (float) $challenge['target'])
        ) * 100
    );
@endphp

<div
    data-lq-theme="sky"
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


        <nav
            class="lq-sidebar-nav"
            aria-label="Main navigation"
        >

            <a
                href="{{ route('child.dashboard') }}"
                class="lq-nav-link is-active"
                aria-current="page"
            >
                <span class="lq-nav-icon" aria-hidden="true">
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
                <span class="lq-nav-icon" aria-hidden="true">
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
                <span class="lq-nav-icon" aria-hidden="true">
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
                <span class="lq-nav-icon" aria-hidden="true">
                    📚
                </span>

                <span>
                    Courses
                </span>
            </a>

        </nav>


        <div class="lq-sidebar-bottom">

            <x-lq.glass-card
                padding="sm"
                class="lq-sidebar-nova"
            >

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

            </x-lq.glass-card>

        </div>

    </aside>


    {{-- ============================================================
         MAIN CONTENT
         ============================================================ --}}

    <main class="lq-main">

        <div class="lq-page">

            {{-- ====================================================
                 TOP BAR
                 ==================================================== --}}

            <header class="lq-topbar">

                <div class="lq-mobile-brand">

                    <div
                        class="lq-brand-mark"
                        aria-hidden="true"
                    >
                        🚀
                    </div>

                    <span>
                        Learn<span>Quest</span>
                    </span>

                </div>


                <div class="lq-top-actions">

                    <x-lq.icon-button
                        size="md"
                        aria-label="Notifications"
                    >
                        🔔
                    </x-lq.icon-button>


                    {{-- Profile --}}
                    <div
                        class="lq-profile-anchor"
                        @click.outside="closeProfile()"
                    >

                        <button
                            type="button"
                            class="lq-profile-trigger lq-avatar lq-avatar--md"
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


                        {{-- Desktop profile menu --}}
                        <div
                            x-cloak
                            x-show="profileOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="lq-profile-menu"
                            role="menu"
                            aria-label="Profile menu"
                        >

                            <div class="lq-profile-summary">

                                <div class="lq-row">

                                    <x-lq.avatar size="md">
                                        {{ $initials }}
                                    </x-lq.avatar>

                                    <div class="lq-grow">

                                        <div class="lq-profile-name">
                                            {{ $explorer['name'] }}
                                        </div>

                                        <div class="lq-profile-level">
                                            Level {{ $explorer['level'] }} Explorer
                                        </div>

                                    </div>

                                </div>


                                <div class="lq-profile-stats">

                                    <div class="lq-profile-stat">

                                        <div class="lq-profile-stat__label">
                                            XP
                                        </div>

                                        <div class="lq-profile-stat__value">
                                            {{ number_format($explorer['xp']) }}
                                        </div>

                                    </div>


                                    <div class="lq-profile-stat">

                                        <div class="lq-profile-stat__label">
                                            Streak
                                        </div>

                                        <div class="lq-profile-stat__value">
                                            🔥 {{ $explorer['streak'] }}
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <div class="lq-profile-menu__items">

                                <a
                                    href="#"
                                    role="menuitem"
                                    class="lq-profile-menu__item"
                                    @click="closeProfile()"
                                >
                                    <span class="lq-profile-menu__item-icon">
                                        👤
                                    </span>

                                    <span>
                                        My Profile
                                    </span>
                                </a>


                                <a
                                    href="#"
                                    role="menuitem"
                                    class="lq-profile-menu__item"
                                    @click="closeProfile()"
                                >
                                    <span class="lq-profile-menu__item-icon">
                                        🏆
                                    </span>

                                    <span>
                                        My Achievements
                                    </span>
                                </a>


                                <a
                                    href="#"
                                    role="menuitem"
                                    class="lq-profile-menu__item"
                                    @click="closeProfile()"
                                >
                                    <span class="lq-profile-menu__item-icon">
                                        ⚙️
                                    </span>

                                    <span>
                                        Settings
                                    </span>
                                </a>

                            </div>


                            <div class="lq-profile-menu__logout">

                                <button
                                    type="button"
                                    class="lq-profile-menu__logout-button"
                                    role="menuitem"
                                    @click="openLogout()"
                                >
                                    <span class="lq-profile-menu__item-icon">
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

                        <span aria-hidden="true">
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

                            <x-lq.button
                                variant="primary"
                                size="md"
                                tag="a"
                                href="#current-mission"
                            >
                                🚀 {{ $mission['buttonText'] }} Quest
                            </x-lq.button>

                        @else

                            <x-lq.button
                                variant="primary"
                                size="md"
                                tag="a"
                                href="{{ route('child.explore') }}"
                            >
                                🧭 Start Adventure
                            </x-lq.button>

                        @endif


                        <x-lq.button
                            variant="secondary"
                            size="md"
                            tag="a"
                            href="#continue-learning"
                            class="lq-hero-secondary"
                        >
                            View Progress
                        </x-lq.button>

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

                <x-lq.stat-chip
                    variant="reward"
                    size="md"
                    class="lq-stat-card"
                    icon="⭐"
                    :value="number_format($explorer['xp'])"
                    label="XP · Growth"
                />


                <x-lq.stat-chip
                    variant="warning"
                    size="md"
                    class="lq-stat-card"
                    icon="🔥"
                    :value="$explorer['streak']"
                    label="Streak · Days"
                />


                <x-lq.stat-chip
                    variant="primary"
                    size="md"
                    class="lq-stat-card"
                    icon="🚀"
                    :value="$explorer['level']"
                    label="Level · Explorer"
                />


                <x-lq.stat-chip
                    variant="success"
                    size="md"
                    class="lq-stat-card"
                    icon="🧭"
                    :value="$explorer['futureReadiness'] . '%'"
                    label="Readiness · Future"
                />

            </section>


            {{-- ====================================================
                 DASHBOARD GRID
                 ==================================================== --}}

            <div class="lq-dashboard-grid">

                {{-- =================================================
                     MAIN COLUMN
                     ================================================= --}}

                <div class="lq-main-column">

                    {{-- Continue Learning --}}
                    <section
                        id="continue-learning"
                        class="lq-section"
                    >

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Pick up where you left off."
                            >
                                Continue Learning
                            </x-lq.section-title>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                Explore
                            </a>

                        </div>


                        <x-lq.glass-card
                            padding="md"
                            variant="glass"
                        >

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


                                    <x-lq.progress-summary
                                        :value="$learning['progress']"
                                        label="Course progress"
                                        color="primary"
                                    />

                                    <a
                                        href="{{ route('child.explore') }}"
                                        class="lq-action"
                                    >
                                        Continue Learning →
                                    </a>

                                </div>

                            @else

                                <x-lq.empty-state
                                    icon="📚"
                                    message="Start your first course to begin your learning journey."
                                />

                            @endif

                        </x-lq.glass-card>

                    </section>


                    {{-- Skills --}}
                    <section class="lq-section">

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Capabilities you're building."
                            >
                                Your Super Skills
                            </x-lq.section-title>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                View all
                            </a>

                        </div>


                        <div class="lq-skill-grid">

                            @forelse($skills as $skill)

                                <x-lq.glass-card
                                    padding="sm"
                                    variant="glass"
                                    class="lq-skill-card"
                                >

                                    <div class="lq-skill-top">

                                        <div class="lq-skill-icon">

                                            @if($skill['icon'])

                                                <x-dynamic-component
                                                    :component="$skill['icon']"
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


                                    <div class="lq-skill-progress">

                                        <x-lq.progress-bar
                                            :value="$skill['progress']"
                                            :max="100"
                                            color="primary"
                                            :show-label="false"
                                        />

                                    </div>

                                </x-lq.glass-card>

                            @empty

                                <div class="lq-card lq-empty lq-grid-full">
                                    Complete activities to build your first skills.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- Learning Worlds --}}
                    <section class="lq-section">

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Choose a world and start your adventure."
                            >
                                Learning Worlds
                            </x-lq.section-title>

                            <a
                                href="{{ route('child.explore') }}"
                                class="lq-see-all"
                            >
                                Explore
                            </a>

                        </div>


                        <div class="lq-world-grid">

                            @forelse($worlds as $world)

                                <x-lq.world-tile
                                    :icon="$world['icon'] ?? '🌎'"
                                    :title="$world['name']"
                                    :description="$world['description'] ?? null"
                                    :progress="$world['progress'] ?? 0"
                                    :xp="$world['xp'] ?? 0"
                                    :badge="$world['badge'] ?? null"
                                    :locked="$world['locked'] ?? false"
                                >

                                    <x-lq.button
                                        variant="secondary"
                                        size="sm"
                                        tag="a"
                                        href="{{ route('child.missions') }}"
                                    >
                                        Explore →
                                    </x-lq.button>

                                </x-lq.world-tile>

                            @empty

                                <div class="lq-card lq-empty lq-grid-full">
                                    New learning worlds are coming soon.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- Recommended Adventures --}}
                    <section class="lq-section">

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="A few quests picked for you."
                            >
                                Recommended Adventures
                            </x-lq.section-title>

                        </div>


                        <div class="lq-adventure-grid">

                            @forelse($recommendations as $adventure)

                                <x-lq.glass-card
                                    padding="md"
                                    variant="glass"
                                    class="lq-adventure-card"
                                >

                                    <div class="lq-row">

                                        <div class="lq-adventure-icon">

                                            @if($adventure['icon'])

                                                <x-dynamic-component
                                                    :component="$adventure['icon']"
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


                                    <p class="lq-card-meta lq-description">
                                        {{ $adventure['description'] }}
                                    </p>


                                    <a
                                        href="{{ route('child.explore') }}"
                                        class="lq-action"
                                    >
                                        Explore →
                                    </a>

                                </x-lq.glass-card>

                            @empty

                                <div class="lq-card lq-empty lq-grid-full">
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

                    {{-- Nova --}}
                    <section class="lq-section">

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Your learning companion."
                            >
                                Nova the Explorer
                            </x-lq.section-title>

                        </div>


                        <x-lq.glass-card
                            padding="md"
                            variant="glass"
                            class="lq-nova"
                        >

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
                                    class="lq-action lq-action--inline"
                                >
                                    {{ $nova['buttonText'] }} →
                                </button>

                            </div>

                        </x-lq.glass-card>

                    </section>


                    {{-- Current Quest --}}
                    <section
                        id="current-mission"
                        class="lq-section"
                    >

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Your next step."
                            >
                                Current Quest
                            </x-lq.section-title>

                        </div>


                        <x-lq.glass-card
                            padding="md"
                            variant="glass"
                        >

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


                                <div class="lq-quest-progress">

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


                                    <x-lq.progress-bar
                                        :value="$mission['progress']"
                                        :max="100"
                                        color="primary"
                                        :show-label="false"
                                    />

                                </div>


                                <a
                                    href="{{ route('child.missions') }}"
                                    class="lq-action lq-action--full"
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

                        </x-lq.glass-card>

                    </section>


                    {{-- Recent Badges --}}
                    <section class="lq-section">

                        <div class="lq-section-head">

                            <x-lq.section-title
                                size="md"
                                subtitle="Milestones you've earned."
                            >
                                Recent Badges
                            </x-lq.section-title>

                        </div>


                        <div class="lq-badge-grid">

                            @forelse($achievements as $achievement)

                                <x-lq.glass-card
                                    padding="sm"
                                    variant="glass"
                                    class="lq-badge-card"
                                >

                                    <div class="lq-badge-icon">

                                        @if($achievement['icon'])

                                            <x-dynamic-component
                                                :component="$achievement['icon']"
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

                                </x-lq.glass-card>

                            @empty

                                <div class="lq-card lq-empty lq-grid-full">
                                    Complete activities to unlock your first badges.
                                </div>

                            @endforelse

                        </div>

                    </section>


                    {{-- Daily Challenge --}}
                    <section class="lq-section">

                        <x-lq.glass-card
                            padding="md"
                            variant="soft"
                            class="lq-challenge"
                        >

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


                            <x-lq.progress-bar
                                :value="$challengeProgress"
                                :max="100"
                                color="reward"
                                :show-label="false"
                                class="lq-challenge-progress"
                            />


                            <button
                                type="button"
                                class="lq-action lq-action--full lq-challenge-action"
                            >
                                {{ $challenge['buttonText'] }}
                            </button>

                        </x-lq.glass-card>

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
            aria-current="page"
        >
            <span aria-hidden="true">🌌</span>
            <span>My Universe</span>
        </a>


        <a
            href="{{ route('child.explore') }}"
            class="lq-mobile-nav-item"
        >
            <span aria-hidden="true">🧭</span>
            <span>Explorer</span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-mobile-nav-item"
        >
            <span aria-hidden="true">🌍</span>
            <span>Worlds</span>
        </a>


        <a
            href="{{ route('child.missions') }}"
            class="lq-mobile-nav-item"
        >
            <span aria-hidden="true">📚</span>
            <span>Courses</span>
        </a>


        <button
            type="button"
            class="lq-mobile-nav-item"
            @click="
                profileOpen
                    ? closeProfile()
                    : openProfile()
            "
            :aria-expanded="profileOpen.toString()"
            aria-haspopup="dialog"
        >
            <span aria-hidden="true">
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
        class="lq-mobile-profile"
        role="dialog"
        aria-modal="true"
        aria-label="Profile menu"
    >

        <div
            class="lq-mobile-profile__backdrop"
            @click="closeProfile()"
        ></div>


        <div
            x-show="profileOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="lq-mobile-profile__sheet"
        >

            <div class="lq-mobile-profile__handle"></div>


            <div class="lq-mobile-profile__summary">

                <div class="lq-row">

                    <x-lq.avatar size="md">
                        {{ $initials }}
                    </x-lq.avatar>

                    <div class="lq-grow">

                        <div class="lq-profile-name">
                            {{ $explorer['name'] }}
                        </div>

                        <div class="lq-profile-level">
                            Level {{ $explorer['level'] }} Explorer
                        </div>

                    </div>

                </div>


                <div class="lq-profile-stats">

                    <div class="lq-profile-stat">

                        <div class="lq-profile-stat__label">
                            XP
                        </div>

                        <div class="lq-profile-stat__value">
                            {{ number_format($explorer['xp']) }}
                        </div>

                    </div>


                    <div class="lq-profile-stat">

                        <div class="lq-profile-stat__label">
                            Streak
                        </div>

                        <div class="lq-profile-stat__value">
                            🔥 {{ $explorer['streak'] }}
                        </div>

                    </div>

                </div>

            </div>


            <div class="lq-mobile-profile__items">

                <a
                    href="#"
                    class="lq-mobile-profile__item"
                    @click="closeProfile()"
                >
                    <span>👤</span>
                    <span>My Profile</span>
                </a>


                <a
                    href="#"
                    class="lq-mobile-profile__item"
                    @click="closeProfile()"
                >
                    <span>🏆</span>
                    <span>My Achievements</span>
                </a>


                <a
                    href="#"
                    class="lq-mobile-profile__item"
                    @click="closeProfile()"
                >
                    <span>⚙️</span>
                    <span>Settings</span>
                </a>


                <button
                    type="button"
                    class="lq-mobile-profile__logout"
                    @click="openLogout()"
                >
                    <span>🚪</span>
                    <span>Log out</span>
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
        class="lq-logout-overlay"
        role="dialog"
        aria-modal="true"
        aria-labelledby="logout-title"
    >

        <div
            x-show="logoutOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="lq-logout-card"
        >

            <div class="lq-logout-icon">
                🚀
            </div>


            <div class="lq-logout-content">

                <h2 id="logout-title">
                    Ready to leave your adventure?
                </h2>

                <p>
                    Your progress is saved. You can come back anytime.
                </p>

            </div>


            <div class="lq-logout-actions">

                <x-lq.button
                    variant="secondary"
                    size="md"
                    type="button"
                    @click="closeLogout()"
                >
                    Stay
                </x-lq.button>


                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >

                    @csrf

                    <x-lq.button
                        variant="secondary"
                        size="md"
                        type="submit"
                    >
                        Log out
                    </x-lq.button>

                </form>

            </div>

        </div>

    </div>


    <livewire:child.layouts.components.floating-actions />

</div>