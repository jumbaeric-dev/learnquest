<header class="border-b border-slate-200 bg-white">

    <div class="flex items-center justify-between px-8 py-5">

        <div>

            <h1 class="text-2xl font-bold">

                {{ config('lqds.name') }}

            </h1>

            <p class="mt-1 text-sm text-slate-500">

                Version {{ config('lqds.version') }}

            </p>

        </div>

        <x-lq.badge variant="achievement">

            Explorer

        </x-lq.badge>

    </div>

</header>