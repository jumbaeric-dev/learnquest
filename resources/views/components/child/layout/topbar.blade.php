@props([
    'explorer' => [],
])

@php
    $name = $explorer['name'] ?? 'Explorer';

    $initials = strtoupper(
        substr($name, 0, 1)
    );
@endphp

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
            Learn<span>Quest</span>
        </span>

    </div>


    {{-- Top actions --}}
    <div class="lq-top-actions">

        {{-- Notifications --}}
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


            {{-- Profile menu --}}
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

                {{-- Profile summary --}}
                <div class="lq-profile-summary">

                    <div class="lq-row">

                        <x-lq.avatar size="md">
                            {{ $initials }}
                        </x-lq.avatar>

                        <div class="lq-grow">

                            <div class="lq-profile-name">
                                {{ $name }}
                            </div>

                            <div class="lq-profile-level">
                                Level {{ $explorer['level'] ?? 1 }} Explorer
                            </div>

                        </div>

                    </div>


                    <div class="lq-profile-stats">

                        <div class="lq-profile-stat">

                            <div class="lq-profile-stat__label">
                                XP
                            </div>

                            <div class="lq-profile-stat__value">
                                {{ number_format($explorer['xp'] ?? 0) }}
                            </div>

                        </div>


                        <div class="lq-profile-stat">

                            <div class="lq-profile-stat__label">
                                Streak
                            </div>

                            <div class="lq-profile-stat__value">
                                🔥 {{ $explorer['streak'] ?? 0 }}
                            </div>

                        </div>

                    </div>

                </div>


                {{-- Profile menu items --}}
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


                {{-- Logout --}}
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