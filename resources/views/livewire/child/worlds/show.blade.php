<x-child.layout.app-shell theme="space">

    <x-child.layout.section-stack>

        <x-child.worlds.world-hero
            :hero="$world['hero']" />

        <livewire:child.worlds.components.adventure-path />

        <!-- @if($world['journey'])
        <x-child.worlds.continue-journey
            :journey="$world['journey']" />
        @endif

        <x-child.worlds.world-progress
            :progress="$world['progress']" />

        <x-child.worlds.course-grid
            :courses="$world['courses']" /> -->

    </x-child.layout.section-stack>

</x-child.layout.app-shell>