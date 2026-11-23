<x-parent-portal.layout.app-shell active="account">
    <div class="space-y-8">
        <header>
            <p class="text-sm font-semibold uppercase tracking-wider text-cyan-300">{{ __('parent_account.eyebrow') }}</p>
            <h1 class="mt-2 text-3xl font-extrabold text-white">{{ __('parent_account.title') }}</h1>
            <p class="mt-2 text-sm text-slate-300">{{ __('parent_account.description') }}</p>
        </header>

        @if (session()->has('parent_profile_saved'))
        <div role="status" class="rounded-xl border border-emerald-300/30 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('parent_profile_saved') }}
        </div>
        @endif

        <section class="rounded-2xl border border-white/10 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">{{ __('parent_account.profile_information') }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ __('parent_account.profile_description') }}</p>
            </div>

            <form wire:submit="saveProfile" class="space-y-5">
                <div>
                    <label for="parent-name" class="mb-1 block text-sm font-semibold text-slate-800">{{ __('parent_account.full_name') }}</label>
                    <input id="parent-name" type="text" wire:model="name" autocomplete="name" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    @error('name') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="parent-email" class="mb-1 block text-sm font-semibold text-slate-800">{{ __('parent_account.email') }}</label>
                    <input id="parent-email" type="email" wire:model="email" autocomplete="email" required maxlength="255"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    @error('email') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="saveProfile"
                        class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-800 disabled:opacity-60">
                        {{ __('parent_account.save_profile') }}
                    </button>
                </div>
            </form>
        </section>

        @if (session()->has('parent_password_saved'))
        <div role="status" class="rounded-xl border border-emerald-300/30 bg-emerald-400/10 px-4 py-3 text-sm font-semibold text-emerald-200">
            {{ session('parent_password_saved') }}
        </div>
        @endif

        <section class="rounded-2xl border border-white/10 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-900">{{ __('parent_account.password_security') }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ __('parent_account.password_description') }}</p>
            </div>

            <form wire:submit="updatePassword" class="space-y-5">
                <div>
                    <label for="current-password" class="mb-1 block text-sm font-semibold text-slate-800">{{ __('parent_account.current_password') }}</label>
                    <input id="current-password" type="password" wire:model="currentPassword" autocomplete="current-password" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    @error('currentPassword') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="new-password" class="mb-1 block text-sm font-semibold text-slate-800">{{ __('parent_account.new_password') }}</label>
                    <input id="new-password" type="password" wire:model="password" autocomplete="new-password" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                    @error('password') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password-confirmation" class="mb-1 block text-sm font-semibold text-slate-800">{{ __('parent_account.confirm_password') }}</label>
                    <input id="password-confirmation" type="password" wire:model="passwordConfirmation" autocomplete="new-password" required
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                </div>

                <div class="flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                        class="inline-flex items-center justify-center rounded-xl bg-cyan-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-800 disabled:opacity-60">
                        {{ __('parent_account.update_password') }}
                    </button>
                </div>
            </form>
        </section>

        <p class="text-sm text-slate-400">
            {{ __('parent_account.manage_plan') }}
            <a href="{{ route('parent.billing') }}" class="font-semibold text-cyan-300 hover:text-cyan-200">{{ __('parent_account.billing_page') }}</a>.
        </p>
    </div>
</x-parent-portal.layout.app-shell>