<x-parent-portal.layout.app-shell active="children">
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Manage children</h1>
            <p class="mt-2 text-slate-300">Create and manage your family's explorer accounts.</p>
        </div>

        @if (session('parent_child_status'))
        <div role="status" class="rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
            {{ session('parent_child_status') }}
        </div>
        @endif

        @if ($generatedPin)
        <section
            id="child-credentials"
            x-ref="credentials"
            class="rounded-2xl border border-cyan-300/30 bg-cyan-400/10 p-5"
            aria-live="polite"
            tabindex="-1"
            x-on:scroll-to-child-credentials.window="$nextTick(() => $refs.credentials?.scrollIntoView({ behavior: 'smooth', block: 'center' }))">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-bold text-white">Child login credentials</h2>
                    <p class="mt-1 text-sm text-slate-300">Share these details privately. The PIN is shown only here and cannot be retrieved later.</p>
                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-400">Username</dt>
                            <dd class="mt-1 font-semibold text-white">{{ $generatedUsername }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-wide text-slate-400">New PIN</dt>
                            <dd class="mt-1 font-mono text-2xl font-extrabold tracking-widest text-cyan-200">{{ $generatedPin }}</dd>
                        </div>
                    </dl>
                </div>
                <button type="button" wire:click="dismissCredentials" class="rounded-lg border border-white/20 px-3 py-2 text-sm font-semibold text-white hover:bg-white/10">Dismiss</button>
            </div>
        </section>
        @endif

        @if ($limitMessage)
        <section role="note" class="rounded-2xl border border-amber-300/30 bg-amber-400/10 p-6">
            <h2 class="font-bold text-white">Child limit reached</h2>
            <p class="mt-1 text-sm text-amber-100">{{ $limitMessage }}</p>
            <a href="{{ route('parent.billing') }}" class="mt-3 inline-flex rounded-lg bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">See plans</a>
        </section>
        @else
        <section class="rounded-2xl border border-white/10 bg-white p-6 text-slate-900 shadow-xl">
            <h2 class="text-xl font-bold">Add a child</h2>
            <p class="mt-1 text-sm text-slate-600">A unique username will be suggested if you leave that field blank. A secure four-digit PIN will be generated.</p>

            <form wire:submit="createChild" class="mt-5 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-slate-700">
                        First name
                        <input wire:model="first_name" type="text" maxlength="50" required autocomplete="given-name" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        @error('first_name') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Last name
                        <input wire:model="last_name" type="text" maxlength="50" autocomplete="family-name" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        @error('last_name') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Username <span class="font-normal text-slate-500">(optional)</span>
                        <input wire:model="username" type="text" maxlength="30" autocomplete="off" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        @error('username') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                    <label class="block text-sm font-semibold text-slate-700">
                        Date of birth <span class="font-normal text-slate-500">(optional)</span>
                        <input wire:model="date_of_birth" type="date" max="{{ now()->subDay()->toDateString() }}" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900 focus:border-cyan-600 focus:outline-none focus:ring-2 focus:ring-cyan-100">
                        @error('date_of_birth') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>
                <label class="block max-w-xs text-sm font-semibold text-slate-700">
                    Explorer avatar
                    <select wire:model="avatar" class="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-slate-900">
                        @foreach ($availableAvatars as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('avatar') <span class="mt-1 block text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
                <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold !text-white shadow-sm transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                    <span wire:loading.remove wire:target="createChild">Create child account</span>
                    <span wire:loading wire:target="createChild">Creating…</span>
                </button>
            </form>
        </section>
        @endif
        
        <section class="space-y-4">
            <div>
                <h2 class="text-xl font-bold text-white">Your children</h2>
                <p class="mt-1 text-sm text-slate-400">Only children connected to your parent account appear here.</p>
            </div>

            @forelse ($children as $child)
            <article wire:key="child-{{ $child->id }}" class="rounded-2xl border border-white/10 bg-white p-5 text-slate-900 shadow-lg">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-3xl">{{ $child->avatar ?: '🚀' }}</span>
                        <div>
                            <h3 class="font-bold text-slate-900">{{ $child->full_name }}</h3>
                            <p class="text-sm text-slate-600">{{ $child->username }} · Level {{ $child->level }} · {{ number_format($child->xp) }} XP</p>
                            <p class="text-xs {{ $child->is_active ? 'text-emerald-700' : 'text-red-700' }}">{{ $child->is_active ? 'Active account' : 'Inactive account' }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="switchToChild({{ $child->id }})" @disabled(!$child->is_active) class="rounded-lg bg-cyan-700 px-3 py-2 text-sm font-semibold text-white hover:bg-cyan-800 disabled:cursor-not-allowed disabled:opacity-50">Switch to child</button>
                        <button type="button" wire:click="beginEdit({{ $child->id }})" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                        <button type="button" wire:click="resetChildPin({{ $child->id }})" wire:confirm="Generate a new PIN? The child's existing PIN will stop working." class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset PIN</button>
                        @if ($child->is_active)
                        <button type="button" wire:click="setChildActive({{ $child->id }}, false)" wire:confirm="Pause {{ $child->first_name }}’s account? They won’t be able to sign in until you resume it." class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Pause</button>
                        @else
                        <button type="button" wire:click="setChildActive({{ $child->id }}, true)" class="rounded-lg border border-emerald-300 px-3 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50">Resume</button>
                        @endif
                        <button type="button" wire:click="askToRemove({{ $child->id }})" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Remove</button>
                    </div>
                </div>

                @if ($editingChildId === $child->id)
                <form wire:submit="saveEdit" class="mt-5 space-y-4 rounded-xl bg-slate-50 p-4">
                    <h4 class="font-bold text-slate-900">Edit explorer profile</h4>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-sm font-semibold text-slate-700">First name
                            <input wire:model="editFirstName" type="text" maxlength="50" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                            @error('editFirstName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Last name
                            <input wire:model="editLastName" type="text" maxlength="50" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                            @error('editLastName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Date of birth
                            <input wire:model="editDateOfBirth" type="date" max="{{ now()->subDay()->toDateString() }}" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                            @error('editDateOfBirth') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                        <label class="text-sm font-semibold text-slate-700">Avatar
                            <select wire:model="editAvatar" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                                @foreach ($availableAvatars as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('editAvatar') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-cyan-700 px-4 py-2 text-sm font-bold text-white">Save changes</button>
                        <button type="button" wire:click="cancelEdit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</button>
                    </div>
                </form>
                @endif

                @if ($confirmDeleteChildId === $child->id)
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4" role="alert">
                    <p class="font-bold text-red-900">Permanently remove {{ $child->first_name }}?</p>
                    <p class="mt-1 text-sm text-red-800">This deletes the child's login and profile, including course and lesson progress, badges, streak history, daily challenge claims and Nova message history. This cannot be undone.</p>
                    <div class="mt-3 flex gap-2">
                        <button type="button" wire:click="removeChild" wire:loading.attr="disabled" class="rounded-lg bg-red-700 px-4 py-2 text-sm font-bold text-red hover:bg-red-800">Permanently remove</button>
                        <button type="button" wire:click="cancelRemove" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-800">Keep child</button>
                    </div>
                </div>
                @endif
            </article>
            @empty
            <div class="rounded-2xl border border-dashed border-white/20 bg-white/5 p-8 text-center">
                <p class="text-3xl">🧑‍🚀</p>
                <h3 class="mt-3 font-bold text-white">No child accounts yet</h3>
                <p class="mt-1 text-sm text-slate-400">Use the form above to add your first explorer.</p>
            </div>
            @endforelse
        </section>
    </div>
</x-parent-portal.layout.app-shell>