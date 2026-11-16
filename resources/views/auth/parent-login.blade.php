<x-parent-portal.auth-card
    title="Parent Sign In"
    subtitle="Follow your explorers' progress">
    <form method="POST" action="{{ route('parent.login.submit') }}" class="space-y-5">
        @csrf

        <x-parent-portal.field
            name="email"
            label="Email"
            type="email"
            autocomplete="email"
            required
            autofocus />

        <x-parent-portal.field
            name="password"
            label="Password"
            type="password"
            autocomplete="current-password"
            required />

        <p class="-mt-2 text-right text-sm">
            <a href="{{ route('parent.password.request') }}" class="font-semibold text-cyan-400 hover:text-cyan-300">
                Forgot your password?
            </a>
        </p>

        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-600 bg-slate-900">
            Keep me signed in
        </label>

        <button
            type="submit"
            class="w-full transform rounded-2xl bg-gradient-to-r from-cyan-500 via-blue-600 to-purple-600 px-6 py-4 text-lg font-extrabold text-white shadow-lg shadow-cyan-500/25 transition duration-150 hover:from-cyan-400 hover:to-purple-500 active:scale-[0.98]">
            Sign In
        </button>
    </form>

    <div class="mt-6 space-y-2 text-center text-sm text-slate-400">
        <p>
            New here?
            <a href="{{ route('parent.register') }}" class="font-semibold text-cyan-400 hover:text-cyan-300">
                Create a parent account
            </a>
        </p>

        <p>
            <a href="{{ route('login') }}" class="hover:text-slate-200">
                I'm an explorer →
            </a>
        </p>
    </div>
</x-parent-portal.auth-card>