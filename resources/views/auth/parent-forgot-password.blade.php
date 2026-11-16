<x-parent-portal.auth-card
    title="Forgot your password?"
    subtitle="We’ll email you a link to reset it"
>
    <form method="POST" action="{{ route('parent.password.email') }}" class="space-y-5">
        @csrf

        <x-parent-portal.field
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            required
            autofocus
        />

        <button
            type="submit"
            class="w-full transform rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 px-6 py-4 text-lg font-extrabold text-white shadow-lg shadow-cyan-500/25 transition duration-150 hover:from-cyan-400 hover:to-purple-500 active:scale-[0.98]"
        >
            Email me a reset link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        <a href="{{ route('parent.login') }}" class="font-semibold text-cyan-400 hover:text-cyan-300">
            Back to sign in
        </a>
    </p>
</x-parent-portal.auth-card>