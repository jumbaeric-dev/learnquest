@if ($show)
<div role="status" class="border-b border-cyan-300/20 bg-cyan-500/10 text-cyan-100">
    <div class="mx-auto flex w-full max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-3 text-sm">
        <p>
            @if (session('verification_status'))
            {{ session('verification_status') }}
            @else
            Please verify your email address ({{ $email }}) so we can send your receipts and reminders.
            @endif
        </p>

        <form method="POST" action="{{ route('parent.verification.send') }}">
            @csrf
            <button type="submit" class="rounded-lg bg-white/15 px-3 py-1.5 font-semibold text-white hover:bg-white/25">
                Resend email
            </button>
        </form>
    </div>
</div>
@endif