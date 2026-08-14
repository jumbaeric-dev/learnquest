@php

use App\Support\Lqds\ComponentRegistry;

$groups = ComponentRegistry::grouped();

@endphp

<nav class="space-y-8">

    @foreach($groups as $category => $components)

        <section>

            <h2
                class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">

                {{ $category }}

            </h2>

            <div class="space-y-1">

                @foreach($components as $component)

                    <a
                        href="#"
                        class="block rounded-lg px-3 py-2 text-sm hover:bg-slate-100">

                        {{ $component['name'] }}

                    </a>

                @endforeach

            </div>

        </section>

    @endforeach

</nav>