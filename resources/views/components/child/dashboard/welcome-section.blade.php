<x-lq.glass-card class="space-y-6" animate>

    <div>

        <h2 class="text-3xl font-bold">

            {{ $welcome['greeting'] }}

        </h2>

        <p class="mt-2 text-lg text-slate-600">

            {{ $welcome['headline'] }}

        </p>

    </div>

    <p class="text-slate-600">

        {{ $welcome['message'] }}

    </p>

    <x-lq.button
        variant="primary"
        size="lg"
    >
        {{ $welcome['buttonText'] }}
    </x-lq.button>

</x-lq.glass-card>