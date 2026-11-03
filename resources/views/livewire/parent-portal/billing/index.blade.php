<x-parent-portal.layout.app-shell active="billing">
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Plan &amp; billing</h1>
            <p class="mt-2 text-slate-300">See what your family’s plan includes.</p>
        </div>

        @unless ($summary['enforced'])
        <div role="note" class="rounded-xl border border-amber-300/20 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">
            Billing hasn’t launched yet, so every feature is currently unlocked for everyone.
        </div>
        @endunless

        <section aria-labelledby="current-plan" class="rounded-2xl border border-white/10 bg-white p-6 text-slate-900 shadow-lg">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Your plan</p>
                    <h2 id="current-plan" class="mt-1 text-2xl font-extrabold">{{ $summary['plan_name'] }}</h2>

                    @if ($summary['ends_at'])
                    <p class="mt-1 text-sm text-slate-600">
                        @if ($summary['status'] === 'trial')
                            Free trial ends
                        @elseif ($summary['status'] === 'canceled')
                            Access ends
                        @else
                            Access until
                        @endif
                        {{ $summary['ends_at']->format('M j, Y') }}
                        ({{ $summary['days_left'] }} {{ \Illuminate\Support\Str::plural('day', $summary['days_left']) }} left)
                    </p>
                    @endif
                </div>

                <div class="text-right text-sm text-slate-600">
                    <p class="font-semibold text-slate-900">Children</p>
                    <p>{{ $summary['children_used'] }} of {{ $summary['children_limit'] ?? 'unlimited' }}</p>
                </div>
            </div>
        </section>

        <section class="space-y-4" aria-labelledby="plans-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="plans-heading" class="text-xl font-bold text-white">Plans</h2>

                <div class="inline-flex rounded-xl border border-white/10 bg-white/5 p-1" role="group" aria-label="Currency">
                    @foreach ($currencies as $option)
                    <button
                        type="button"
                        wire:click="setCurrency('{{ $option }}')"
                        aria-pressed="{{ $currency === $option ? 'true' : 'false' }}"
                        @class([
                            'rounded-lg px-3 py-1.5 text-sm font-semibold transition',
                            'bg-white/15 text-white' => $currency === $option,
                            'text-slate-300 hover:text-white' => $currency !== $option,
                        ])
                    >{{ $option }}</button>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                @foreach ($plans as $plan)
                <article wire:key="plan-{{ $plan['key'] }}" class="flex flex-col rounded-2xl border border-white/10 bg-white p-6 text-slate-900 shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-xl font-extrabold">{{ $plan['name'] }}</h3>
                            @if ($plan['tagline'])
                            <p class="mt-1 text-sm text-slate-600">{{ $plan['tagline'] }}</p>
                            @endif
                        </div>

                        @if ($plan['is_current'])
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Current plan</span>
                        @endif
                    </div>

                    <ul class="mt-4 space-y-2 text-sm text-slate-700">
                        @foreach ($plan['highlights'] as $highlight)
                        <li class="flex gap-2"><span aria-hidden="true">✅</span><span>{{ $highlight }}</span></li>
                        @endforeach
                    </ul>

                    <div class="mt-5 flex-1">
                        @forelse ($plan['prices'] as $price)
                        <div class="flex items-center justify-between border-t border-slate-200 py-2 text-sm">
                            <span class="text-slate-600">{{ $price['label'] }}</span>
                            <span class="font-bold">{{ $price['amount'] }}</span>
                        </div>
                        @empty
                        <p class="border-t border-slate-200 pt-3 text-sm font-semibold text-slate-700">Free forever</p>
                        @endforelse
                    </div>

                    @if (! empty($plan['prices']) && ! $plan['is_current'])
                    <button type="button" disabled class="mt-4 w-full cursor-not-allowed rounded-xl bg-slate-200 px-4 py-3 text-sm font-bold text-slate-500">
                        Upgrade, coming soon
                    </button>
                    <p class="mt-2 text-xs text-slate-500">Secure checkout with M-Pesa, card and PayPal is on the way.</p>
                    @endif
                </article>
                @endforeach
            </div>
        </section>
    </div>
</x-parent-portal.layout.app-shell>