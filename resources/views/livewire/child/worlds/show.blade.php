<x-child.layout.app-shell theme="space">

    <x-child.layout.section-stack>

        <x-child.worlds.world-hero
            :hero="$world['hero']"
            :journey="$world['journey']" />

        @if($world['journey'])
        <x-child.worlds.continue-journey
            :journey="$world['journey']" />
        @endif

        <x-child.worlds.world-progress
            :progress="$world['progress']" />

        <livewire:child.worlds.components.adventure-path :courses="$world['courses']" />

    </x-child.layout.section-stack>

</x-child.layout.app-shell>