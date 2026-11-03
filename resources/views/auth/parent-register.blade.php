<x-parent-portal.auth-card
    title="Create Parent Account"
    subtitle="Set up your family on LearnQuest">
    <form method="POST" action="{{ route('parent.register.submit') }}" class="space-y-5">
        @csrf

        <x-parent-portal.field
            name="name"
            label="Your name"
            autocomplete="name"
            required
            autofocus />

        <x-parent-portal.field
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            required />

        <x-parent-portal.field
            name="password"
            label="Password (min. 8 characters)"
            type="password"
            autocomplete="new-password"
            required />

        <x-parent-portal.field
            name="password_confirmation"
            label="Confirm password"
            type="password"
            autocomplete="new-password"
            required />

        <button
            type="submit"
            class="w-full transform rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 px-6 py-4 text-lg font-extrabold text-white shadow-lg shadow-cyan-500/25 transition duration-150 hover:from-cyan-400 hover:to-purple-500 active:scale-[0.98]">
            Create Account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        Already have an account?
        <a href="{{ route('parent.login') }}" class="font-semibold text-cyan-400 hover:text-cyan-300">
            Sign in
        </a>
    </p>
</x-parent-portal.auth-card>