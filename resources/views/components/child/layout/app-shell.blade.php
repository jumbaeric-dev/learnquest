
@props([
'theme' => 'sky',
'explorer' => [],
'active' => null,
])

@php
    // Resolve the active sidebar item from the route unless the page explicitly
    // passes an active key. Desktop and mobile both use this shared sidebar.
    $active = $active ?? match (true) {
        request()->routeIs('child', 'child.dashboard') => 'dashboard',
        request()->routeIs('child.explore')
            && request()->query('section') === 'worlds' => 'worlds',
        request()->routeIs('child.explore') => 'explore',
        request()->routeIs('child.world') => 'worlds',
        request()->routeIs(
            'child.missions',
            'child.mission',
            'learn.course',
            'learn.lesson',
            'learn.activity'
        ) => 'courses',
        default => null,
    };
@endphp

<div
    data-lq-theme="{{ $theme }}"
    class="lq-universe lq-universe-shell"
    x-data="{
        mobileMenuOpen: false,
        profileOpen: false,
        logoutOpen: false,

        toggleMobileMenu() {
            this.mobileMenuOpen = !this.mobileMenuOpen;
        },

        closeMobileMenu() {
            this.mobileMenuOpen = false;
        },

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
        if (mobileMenuOpen) {
            mobileMenuOpen = false;
        } else if (logoutOpen) {
            closeLogout();
        } else if (profileOpen) {
            closeProfile();
        }
    ">

    <x-child.layout.background :theme="$theme" />

    @if(session()->has('message'))
    <div class="fixed top-5 right-5 z-[120]">
        <x-lq.badge>
            {{ session('message') }}
        </x-lq.badge>
    </div>
    @endif

    {{-- Mobile navigation backdrop. Kept before the sidebar so the drawer stays above it. --}}
    <button
        type="button"
        x-cloak
        x-show="mobileMenuOpen"
        x-transition.opacity.duration.150ms
        class="lq-mobile-nav-backdrop"
        aria-label="{{ __('localization.close_navigation') }}"
        @click="mobileMenuOpen = false">
    </button>

    <x-child.layout.sidebar
        :active="$active" />

    @if (auth()->check() && auth()->user()->isParent())
        <div class="relative z-[110] flex items-center justify-between gap-3 bg-cyan-950 px-4 py-2 text-sm text-white">
            <span>{{ __('localization.parent_preview', ['name' => app(\App\Services\Child\Context\CurrentChildService::class)->current()?->full_name]) }}</span>
            <form method="POST" action="{{ route('parent.return') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-white/10 px-3 py-1.5 font-semibold hover:bg-white/20">{{ __('localization.return_parent_dashboard') }}</button>
            </form>
        </div>
    @endif

    <main class="lq-main">

        <x-child.layout.topbar
            :explorer="$explorer" />

        <div class="lq-page">

            {{ $slot }}

        </div>

    </main>

    <livewire:child.layouts.components.floating-actions />

    <livewire:child.nova.chat-widget />

</div>
