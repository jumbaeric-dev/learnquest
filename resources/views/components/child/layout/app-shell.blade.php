@props([
'theme' => 'sky',
'explorer' => [],
'active' => 'dashboard',
])

<div
    data-lq-theme="{{ $theme }}"
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
    ">

    <x-child.layout.background :theme="$theme" />

    @if(session()->has('message'))
    <div class="fixed top-5 right-5 z-[120]">
        <x-lq.badge>
            {{ session('message') }}
        </x-lq.badge>
    </div>
    @endif

    <x-child.layout.sidebar
        :active="$active" />

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