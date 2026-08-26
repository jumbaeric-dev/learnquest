@props([
    'theme' => 'sky',
])

<div class="relative min-h-screen overflow-hidden">

    <x-child.layout.background :theme="$theme" />

    {{-- Authenticated Child Header --}}
    <header class="relative z-20 px-4 pt-4 sm:px-6">
        <div class="mx-auto flex max-w-7xl justify-end">
            <x-child.layout.logout />
        </div>
    </header>

    <main class="relative z-10">

        <x-child.layout.page-container>

            {{ $slot }}

        </x-child.layout.page-container>

    </main>

    <livewire:child.layouts.components.floating-actions />

    <livewire:child.layouts.components.bottom-navigation />

</div>