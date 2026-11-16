@if ($notice)
<div
    role="status"
    @class([
        'border-b',
        'border-amber-300/20 bg-amber-500/15 text-amber-100' => $notice['level'] === 'warning',
        'border-rose-300/20 bg-rose-500/15 text-rose-100' => $notice['level'] === 'danger',
    ])
>
    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
        <p>{{ $notice['message'] }}</p>

        <a
            href="{{ route('parent.billing') }}"
            class="rounded-lg bg-white/15 px-3 py-1.5 font-semibold text-white hover:bg-white/25"
        >
            {{ $notice['cta'] }}
        </a>
    </div>
</div>
@endif