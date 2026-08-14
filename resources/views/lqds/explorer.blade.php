{{-- resources/views/lqds/explorer.blade.php --}}

<x-lqds.layout>

    <x-lqds.page-title
        title="LearnQuest Design System"
        description="The official component library and documentation for the LearnQuest platform."
    />

    <div class="space-y-8">

        {{-- Welcome --------------------------------------------------------- --}}
        <x-lqds.section
            title="Welcome"
            description="The Design System Explorer is the single source of truth for LearnQuest UI components."
        >

            <div class="grid gap-6 md:grid-cols-4">

                <x-lq.stat-chip
                    label="Components"
                    :value="count(config('lqds.components'))"
                />

                <x-lq.stat-chip
                    label="Version"
                    :value="config('lqds.version')"
                />

                <x-lq.stat-chip
                    label="Framework"
                    value="Laravel 13"
                />

                <x-lq.stat-chip
                    label="Status"
                    value="Stable"
                />

            </div>

        </x-lqds.section>

        {{-- Registered Components -------------------------------------------- --}}
        <x-lqds.section
            title="Registered Components"
            description="Every reusable component currently available in LQDS."
        >

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                @foreach(config('lqds.components') as $componentDefinition)

                    <x-lq.glass-card>

                        <div class="flex items-start justify-between">

                            <div>

                                <h3 class="text-lg font-semibold text-slate-900">
                                    {{ $componentDefinition['name'] }}
                                </h3>

                                <p class="mt-2 text-sm text-slate-600">
                                    {{ $componentDefinition['description'] }}
                                </p>

                            </div>

                            <x-lq.badge>
                                {{ $componentDefinition['status'] }}
                            </x-lq.badge>

                        </div>

                        <div class="mt-6 space-y-2">

                            <div>

                                <span class="text-xs uppercase tracking-wide text-slate-500">
                                    Blade Component
                                </span>

                                <code class="mt-1 block rounded bg-slate-100 px-2 py-1 text-sm">
                                    &lt;{{ $componentDefinition['tag'] }} /&gt;
                                </code>

                            </div>

                            <div>

                                <span class="text-xs uppercase tracking-wide text-slate-500">
                                    Category
                                </span>

                                <p class="text-sm">
                                    {{ $componentDefinition['category'] }}
                                </p>

                            </div>

                        </div>

                    </x-lq.glass-card>

                @endforeach

            </div>

        </x-lqds.section>

        {{-- Live Preview ----------------------------------------------------- --}}
        <x-lqds.section
            title="Live Component Preview"
            description="A quick preview of the currently implemented production components."
        >

            <x-lqds.component-preview
                title="Badge"
                description="Status and metadata labels."
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

        </x-lqds.section>

        {{-- Roadmap ---------------------------------------------------------- --}}
        <x-lqds.section
            title="Roadmap"
            description="Planned enhancements for future versions of LQDS."
        >

            <div class="grid gap-4 md:grid-cols-2">

                <x-lq.glass-card>

                    <h3 class="font-semibold">
                        Version 1.1
                    </h3>

                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li>✓ Component documentation pages</li>
                        <li>✓ Interactive examples</li>
                        <li>✓ Props documentation</li>
                        <li>✓ Usage examples</li>
                    </ul>

                </x-lq.glass-card>

                <x-lq.glass-card>

                    <h3 class="font-semibold">
                        Future
                    </h3>

                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li>• Design tokens</li>
                        <li>• Theme support</li>
                        <li>• Search</li>
                        <li>• Component playground</li>
                        <li>• Accessibility testing</li>
                    </ul>

                </x-lq.glass-card>

            </div>

        </x-lqds.section>

    </div>

</x-lqds.layout>