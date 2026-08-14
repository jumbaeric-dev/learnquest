<x-child.layout.app-shell theme="space">

    <x-child.worlds.world-hero
        :hero="$world['hero']"
    />

    @if($world['journey'])
        <x-child.worlds.continue-journey
            :journey="$world['journey']"
        />
    @endif

    <x-child.worlds.world-progress
        :progress="$world['progress']"
    />

    <x-child.worlds.course-grid
        :courses="$world['courses']"
    />

</x-child.layout.app-shell>