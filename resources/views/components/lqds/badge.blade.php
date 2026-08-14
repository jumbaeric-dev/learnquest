<x-lqds.layout>

    <x-lqds.page-title
        title="Badge"
        description="Displays short labels used for status, categories, and achievements."
    />

    <x-lqds.component-preview
        title="Variants"
        description="Examples of the available badge variants."
    >

        <div class="flex flex-wrap gap-4">

            <x-lq.badge>
                Default
            </x-lq.badge>

            <x-lq.badge variant="achievement">
                ⭐ Achievement
            </x-lq.badge>

            <x-lq.badge variant="locked">
                Locked
            </x-lq.badge>

        </div>

    </x-lqds.component-preview>

    <div class="mt-8">

        <x-lqds.props-table
            :props="[
                [
                    'name' => 'variant',
                    'type' => 'string',
                    'default' => 'neutral',
                    'description' => 'Visual style of the badge.',
                ],
                [
                    'name' => 'size',
                    'type' => 'string',
                    'default' => 'md',
                    'description' => 'Controls badge size.',
                ],
            ]"
        />

    </div>

</x-lqds.layout>