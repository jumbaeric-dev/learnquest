@props([
    'title',
    'description' => null,
])

<section class="mb-12">

    <h2 class="text-2xl font-bold text-slate-900">
        {{ $title }}
    </h2>

    @if($description)

        <p class="mt-2 text-slate-600">
            {{ $description }}
        </p>

    @endif

    <div class="mt-6">

        {{ $slot }}

    </div>

</section>