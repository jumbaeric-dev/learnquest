<x-parent-portal.auth-card
    title="Choose a new password"
    subtitle="Pick something at least 8 characters long"
>
    <form method="POST" action="{{ route('parent.password.update') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        @if ($email !== '')
            <p class="text-center text-sm text-slate-300">Resetting the password for {{ $email }}</p>
        @endif

        @error('email')
            <p class="rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-100">{{ $message }}</p>
        @enderror

        <x-parent-portal.field
            name="password"
            label="New password"
            type="password"
            autocomplete="new-password"
            required
            autofocus
        />

        <x-parent-portal.field
            name="password_confirmation"
            label="Confirm new password"
            type="password"
            autocomplete="new-password"
            required
        />

        <button
            type="submit"
            class="w-full transform rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 px-6 py-4 text-lg font-extrabold text-white shadow-lg shadow-cyan-500/25 transition duration-150 hover:from-cyan-400 hover:to-purple-500 active:scale-[0.98]"
        >
            Reset password
        </button>
    </form>
</x-parent-portal.auth-card>