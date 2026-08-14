@props([
    'title',
    'description' => null,
])

<div class="mb-10">

    <h1 class="text-3xl font-bold text-slate-900">
        {{ $title }}
    </h1>

    @if($description)
        <p class="mt-2 max-w-3xl text-slate-600">
            {{ $description }}
        </p>
    @endif

</div>