@props([
    'theme' => 'sky',
])

<div
    data-lq-theme="{{ $theme }}"
    class="lq-app-shell"
>
    <x-child.layout.background :theme="$theme" />

    <main class="relative z-10">
        <x-child.layout.page-container>
            {{ $slot }}
        </x-child.layout.page-container>
    </main>

    <livewire:child.layouts.components.floating-actions />

    @unless(request()->routeIs('child.dashboard', 'child'))
        <livewire:child.layouts.components.bottom-navigation />
    @endunless
</div>