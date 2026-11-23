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

    {{-- Mobile navigation toggle --}}
    <button
        type="button"
        class="lq-mobile-menu-trigger"
        aria-label="{{ __('localization.toggle_navigation') }}"
        aria-controls="child-navigation"
        :aria-expanded="mobileMenuOpen.toString()"
        @click="toggleMobileMenu()">
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    {{-- Mobile brand --}}
    <div class="lq-mobile-brand">

        <div
            class="lq-brand-mark"
            aria-hidden="true">
            🚀
        </div>

        <span>
            Learn<span>Quest</span>
        </span>

    </div>


    {{-- Top actions --}}
    <div class="lq-top-actions">

        <x-localization.language-switcher />

        {{-- Notifications --}}
        <x-lq.icon-button
            size="md"
            aria-label="{{ __('localization.notifications') }}">
            🔔
        </x-lq.icon-button>


        {{-- Profile --}}
        <div
            class="lq-profile-anchor"
            @click.outside="closeProfile()">

            <button
                type="button"
                class="lq-profile-trigger lq-avatar lq-avatar--md"
                aria-label="{{ __('localization.open_profile') }}"
                aria-haspopup="menu"
                :aria-expanded="profileOpen.toString()"
                @click="
                    profileOpen
                        ? closeProfile()
                        : openProfile()
                ">
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
                aria-label="{{ __('localization.profile_menu') }}">

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
                                {{ __('localization.level_explorer', ['level' => $explorer['level'] ?? 1]) }}
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
                                {{ __('localization.streak') }}
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
                        href="{{ route('child.profile') }}"
                        role="menuitem"
                        class="lq-profile-menu__item"
                        @click="closeProfile()">
                        <span class="lq-profile-menu__item-icon">
                            👤
                        </span>

                        <span>
                            {{ __('localization.my_profile') }}
                        </span>
                    </a>

                    <a
                        href="{{ route('child.dashboard') }}#recent-achievements"
                        role="menuitem"
                        class="lq-profile-menu__item"
                        @click="closeProfile()">
                        <span class="lq-profile-menu__item-icon">
                            🏆
                        </span>

                        <span>
                            {{ __('localization.my_achievements') }}
                        </span>
                    </a>



                    <a
                        href="{{ route('child.settings') }}"
                        role="menuitem"
                        class="lq-profile-menu__item"
                        @click="closeProfile()">
                        <span class="lq-profile-menu__item-icon">
                            ⚙️
                        </span>

                        <span>
                            {{ __('localization.settings_menu') }}
                        </span>
                    </a>


                </div>

                {{-- Logout --}}
                <div class="lq-profile-menu__logout">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button
                            type="submit"
                            class="lq-profile-menu__logout-button"
                            role="menuitem">
                            <span class="lq-profile-menu__item-icon">
                                🚪
                            </span>

                            <span>
                                {{ __('localization.log_out') }}
                            </span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</header>