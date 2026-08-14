<div class="px-4">

    <x-lq.section-title
        title="Learning Worlds"
        subtitle="Choose your next adventure" />


    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">


        @foreach($worlds as $world)

        <x-lq.world-tile

            :title="$world['title']"

            :description="$world['description']"

            :icon="$world['icon']"

            :progress="$world['progress']"

            :xp="$world['xp']"

            :theme="$world['theme']"

            :locked="$world['locked']"

            :badge="$world['badge']">

            <a href="{{ route('child.world', $world['slug']) }}">

                <x-lq.button>
                    🚀 Enter World
                </x-lq.button>

            </a>


        </x-lq.world-tile>


        @endforeach


    </div>

</div>