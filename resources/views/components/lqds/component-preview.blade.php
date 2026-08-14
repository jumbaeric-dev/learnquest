@props([
    'title',
    'description' => null,
])

<x-lq.glass-card>

    <div class="mb-6">

        <h2 class="text-lg font-semibold">

            {{ $title }}

        </h2>

        @if($description)

            <p class="mt-2 text-sm text-slate-500">

                {{ $description }}

            </p>

        @endif

    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6">

        {{ $slot }}

    </div>

</x-lq.glass-card>