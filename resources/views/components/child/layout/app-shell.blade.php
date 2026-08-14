@props([
'theme' => 'sky',
])

<div class="relative min-h-screen overflow-hidden">

    <x-child.layout.background :theme="$theme" />

    <main class="relative z-10">

        <x-child.layout.page-container>

            {{ $slot }}

        </x-child.layout.page-container>

    </main>

    <livewire:child.layouts.components.floating-actions />

    <livewire:child.layouts.components.bottom-navigation />

</div>