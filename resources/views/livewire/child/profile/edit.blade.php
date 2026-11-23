<x-child.layout.app-shell
    theme="sky"
    :explorer="[
        'name' => trim($first_name . ' ' . $last_name),
        'level' => app(\App\Services\Child\Context\CurrentChildService::class)->current()?->level ?? 1,
        'xp' => app(\App\Services\Child\Context\CurrentChildService::class)->current()?->xp ?? 0,
        'streak' => app(\App\Services\Child\Context\CurrentChildService::class)->current()?->streak?->current_streak ?? 0,
    ]">
    <x-child.layout.section-stack spacing="relaxed">

        {{-- Heading --}}
        <section>
            <a
                href="{{ route('child.profile') }}"
                class="inline-flex items-center gap-2 text-sm font-semibold text-sky-700 hover:underline">
                <span aria-hidden="true">←</span>
                {{ __('localization.back_to_my_profile') }}
            </a>

            <p class="mt-5 text-sm font-semibold uppercase tracking-widest text-sky-600">
                {{ __('localization.make_it_yours') }}
            </p>

            <h1 class="mt-1 text-3xl font-extrabold tracking-tight">
                {{ __('localization.edit_my_profile') }}
            </h1>

            <p class="mt-2 text-sm opacity-75">
                {{ __('localization.edit_profile_description') }}
            </p>
        </section>

        {{-- Form --}}
        <form wire:submit="save" class="space-y-6">

            {{-- Avatar selection --}}
            <section class="lq-glass-card rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-extrabold">
                    {{ __('localization.choose_explorer_avatar') }}
                </h2>

                <p class="mt-1 text-sm opacity-70">
                    {{ __('localization.pick_representative_character') }}
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    @foreach ($availableAvatars as $option)
                    <button
                        type="button"
                        wire:key="avatar-{{ $loop->index }}"
                        wire:click="$set('avatar', '{{ $option }}')"
                        aria-label="{{ __('localization.choose_avatar', ['avatar' => $option]) }}"
                        aria-pressed="{{ $avatar === $option ? 'true' : 'false' }}"
                        class="flex h-14 w-14 items-center justify-center rounded-2xl border-2 text-3xl transition hover:-translate-y-0.5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $avatar === $option ? 'border-sky-500 bg-sky-100 shadow-sm' : 'border-slate-200 bg-white/70' }}">
                        {{ $option }}
                    </button>
                    @endforeach
                </div>

                @error('avatar')
                <p class="mt-3 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </section>

            {{-- Personal details --}}
            <section class="lq-glass-card rounded-3xl border border-sky-100 p-6 shadow-sm sm:p-8">
                <h2 class="text-lg font-extrabold">
                    {{ __('localization.my_details') }}
                </h2>

                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label for="first_name" class="mb-2 block text-sm font-semibold">
                            {{ __('localization.first_name') }}
                        </label>

                        <input
                            id="first_name"
                            type="text"
                            wire:model="first_name"
                            maxlength="50"
                            autocomplete="given-name"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">

                        @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="mb-2 block text-sm font-semibold">
                            {{ __('localization.last_name') }}
                        </label>

                        <input
                            id="last_name"
                            type="text"
                            wire:model="last_name"
                            maxlength="50"
                            autocomplete="family-name"
                            class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100">

                        @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="date_of_birth" class="mb-2 block text-sm font-semibold">
                            {{ __('localization.birthday') }}
                        </label>

                        <input
                            id="date_of_birth"
                            type="date"
                            wire:model="date_of_birth"
                            max="{{ now()->subDay()->toDateString() }}"
                            autocomplete="bday"
                            class="w-full rounded-xl border border-slate-200 bg-white/80 px-4 py-3 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100 sm:max-w-md">

                        <p class="mt-2 text-xs opacity-65">
                            {{ __('localization.birthday_personalise') }}
                        </p>

                        @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- Actions --}}
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <a
                    href="{{ route('child.profile') }}"
                    class="inline-flex min-h-12 items-center justify-center rounded-xl border border-slate-200 bg-white/70 px-6 py-3 font-semibold transition hover:bg-white">
                    {{ __('localization.cancel') }}
                </a>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-sky-600 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">
                        {{ __('localization.save_changes') }}
                    </span>

                    <span wire:loading wire:target="save">
                        {{ __('localization.saving') }}
                    </span>
                </button>

            </div>

        </form>

    </x-child.layout.section-stack>
</x-child.layout.app-shell>