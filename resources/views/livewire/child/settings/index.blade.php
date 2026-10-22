
<x-child.layout.app-shell theme="sky">
    <x-child.layout.section-stack spacing="relaxed">

        {{-- Page heading --}}
        <section>
            <a
                href="{{ route('child.dashboard') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700 hover:underline"
            >
                <span aria-hidden="true">←</span>
                Back to My Universe
            </a>

            <p class="mt-5 text-sm font-semibold uppercase tracking-widest text-sky-600">
                Make learning yours
            </p>

            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">
                Settings
            </h1>

            <p class="mt-2 text-sm opacity-75">
                Choose how you'd like your LearnQuest adventures to feel.
            </p>
        </section>

        {{-- Save confirmation --}}
        @if (session()->has('settings_saved'))
            <div
                role="status"
                class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800"
            >
                ✅ {{ session('settings_saved') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">

            {{-- Learning experience --}}
            <section class="lq-glass-card rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
                <div class="flex items-start gap-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-2xl"
                        aria-hidden="true"
                    >
                        🎮
                    </span>

                    <div>
                        <h2 class="text-xl font-extrabold">
                            Learning Experience
                        </h2>

                        <p class="mt-1 text-sm opacity-70">
                            Personalise some of your learning preferences.
                        </p>
                    </div>
                </div>

                {{-- Sound effects --}}
                <div class="mt-6 flex items-center justify-between gap-4 border-t border-slate-200/70 pt-5">
                    <div class="flex-1">
                        <label
                            for="soundEffects"
                            class="font-semibold"
                        >
                            🔊 Sound effects
                        </label>

                        <p class="mt-1 text-sm opacity-70">
                            Choose whether you'd like sound effects in supported activities.
                        </p>
                    </div>

                    <input
                        id="soundEffects"
                        type="checkbox"
                        wire:model="soundEffects"
                        role="switch"
                        class="h-5 w-5 shrink-0 accent-sky-600"
                    >
                </div>

                {{-- Reduced animations --}}
                <div class="mt-5 flex items-center justify-between gap-4 border-t border-slate-200/70 pt-5">
                    <div class="flex-1">
                        <label
                            for="reducedAnimations"
                            class="font-semibold"
                        >
                            ✨ Reduce animations
                        </label>

                        <p class="mt-1 text-sm opacity-70">
                            Save your preference for simpler movement and transitions.
                        </p>
                    </div>

                    <input
                        id="reducedAnimations"
                        type="checkbox"
                        wire:model="reducedAnimations"
                        role="switch"
                        class="h-5 w-5 shrink-0 accent-sky-600"
                    >
                </div>
            </section>

            {{-- Account information --}}
            <section class="lq-glass-card rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
                <div class="flex items-start gap-4">
                    <span
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-2xl"
                        aria-hidden="true"
                    >
                        🛡️
                    </span>

                    <div class="flex-1">
                        <h2 class="text-xl font-extrabold">
                            Account & Privacy
                        </h2>

                        <p class="mt-2 text-sm leading-6 opacity-75">
                            Some account and privacy changes need help from your parent or caregiver.
                            Your login details and account security cannot be changed here.
                        </p>

                        <a
                            href="{{ route('child.profile') }}"
                            class="mt-4 inline-flex items-center gap-2 font-semibold text-sky-700 hover:underline"
                        >
                            View My Profile
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('child.dashboard') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-6 py-3 font-semibold transition hover:bg-white"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-sky-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="save">
                        Save Settings
                    </span>

                    <span wire:loading wire:target="save">
                        Saving...
                    </span>
                </button>
            </div>

        </form>

    </x-child.layout.section-stack>
</x-child.layout.app-shell>
