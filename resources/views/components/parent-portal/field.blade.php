@props([
'name',
'label',
'type' => 'text',
'autocomplete' => null,
])

<div class="text-left">
    <label
        for="{{ $name }}"
        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-300">
        {{ $label }}
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if($type !=='password' ) value="{{ old($name) }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-2xl border border-slate-700 bg-slate-900/80 px-4 py-3.5 text-white placeholder-slate-500 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-cyan-400',
        ]) }}>

    @error($name)
    <span class="mt-1 block text-xs text-rose-400">{{ $message }}</span>
    @enderror
</div>