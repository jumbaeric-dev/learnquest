<x-child.layout.app-shell theme="space">
    <div
        class="
        max-w-7xl
        mx-auto
        px-4
        pb-32
        space-y-8
    ">
        @if(session()->has('message'))

        <div
            class="
fixed
top-5
right-5
z-50
">
            <x-lq.badge>
                {{ session('message') }}
            </x-lq.badge>

        </div>

        @endif

        {{-- Header --}}
        <livewire:child.missions.components.mission-header />

        {{-- Featured Mission --}}
        <livewire:child.missions.components.featured-mission />

        {{-- Categories --}}
        <livewire:child.missions.components.mission-categories
            :selected-category="$selectedCategory" />

        {{-- Missions --}}
        <div class="grid
grid-cols-1
sm:grid-cols-2
lg:grid-cols-3
gap-5">

            @foreach($missions as $mission)

            <livewire:child.missions.components.mission-card
                :mission="$mission"
                :key="$mission['title']" />

            @endforeach

        </div>

        <livewire:child.missions.components.daily-challenges />

    </div>
</x-child.layout.app-shell>