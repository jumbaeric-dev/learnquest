<x-parent-portal.layout.app-shell active="billing">
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white">Plan &amp; billing</h1>
            <p class="mt-2 text-slate-300">See what your family’s plan includes.</p>
        </div>

        @if ($notice)
        <div role="status" class="rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-100">
            {{ $notice }}
        </div>
        @endif

        @if ($pendingCount > 0)
        <div role="status" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-cyan-300/20 bg-cyan-400/10 px-4 py-3 text-sm text-cyan-100">
            <span>We’re confirming your payment. If you’ve already paid, this usually takes a minute.</span>
            <button type="button" wire:click="refreshPayments" wire:loading.attr="disabled" class="rounded-lg bg-white/10 px-3 py-1.5 font-semibold text-white hover:bg-white/20">
                Check status
            </button>
        </div>
        @endif

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

                    @if ($summary['status'] === 'grace')
                    <p class="mt-1 text-sm text-amber-700">
                        Ended {{ $summary['ends_at']->format('M j, Y') }}.
                        Your access continues until {{ $summary['grace_ends_at']->format('M j, Y') }}.
                        Add time to keep it going.
                    </p>
                    @elseif ($summary['ends_at'])
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
                        @class([ 'rounded-lg px-3 py-1.5 text-sm font-semibold transition' , 'bg-white/15 text-white'=> $currency === $option,
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

                    @if (! empty($plan['prices']))
                    @if ($canUseCheckout)
                    <div class="mt-4 space-y-3">
                        @foreach ($plan['prices'] as $price)
                        <button
                            type="button"
                            wire:key="checkout-price-{{ $price['id'] }}"
                            wire:click="startCheckout({{ $price['id'] }})"
                            wire:loading.attr="disabled"
                            wire:target="startCheckout"
                            class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60">
                            <span wire:loading.remove wire:target="startCheckout">
                                @if ($plan['is_current'] && in_array($summary['status'], ['active', 'canceled', 'grace'], true))
                                Add {{ $price['label'] }} · {{ $price['amount'] }}
                                @else
                                Choose {{ $price['label'] }} · {{ $price['amount'] }}
                                @endif
                            </span>
                            <span wire:loading wire:target="startCheckout">
                                Connecting to secure checkout…
                            </span>
                        </button>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-slate-500">
                        You’ll complete payment securely on {{ $checkoutProviderLabel }}. Your plan changes after payment is verified.
                    </p>
                    @if ($plan['is_current'] && in_array($summary['status'], ['active', 'canceled', 'grace'], true))
                    <p class="mt-2 text-xs text-slate-500">
                        @if ($summary['status'] === 'grace')
                        Your new time continues from the day your plan ended, so there’s no gap.
                        @else
                        Time you add is stacked onto your current end date, so nothing is lost.
                        @endif
                    </p>
                    @endif
                    @else
                    <p class="mt-4 rounded-xl bg-slate-100 px-4 py-3 text-sm text-slate-600">
                        Online checkout is not currently available for {{ $currency }}.
                    </p>
                    @endif
                    @endif

                    @error('checkout')
                    <p role="alert" class="mt-3 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </p>
                    @enderror
                </article>
                @endforeach
            </div>
        </section>

        @php
        $statusLabels = [
        'succeeded' => 'Paid',
        'pending' => 'Confirming',
        'processing' => 'Confirming',
        'failed' => 'Didn’t go through',
        'refunded' => 'Refunded',
        'partially_refunded' => 'Partly refunded',
        ];
        $statusStyles = [
        'succeeded' => 'bg-emerald-100 text-emerald-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'processing' => 'bg-amber-100 text-amber-800',
        'failed' => 'bg-rose-100 text-rose-800',
        'refunded' => 'bg-slate-200 text-slate-700',
        'partially_refunded' => 'bg-slate-200 text-slate-700',
        ];
        @endphp

        <section class="space-y-4" aria-labelledby="history-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="history-heading" class="text-xl font-bold text-white">Payment history</h2>

                @if ($historyTotal > 5)
                <button type="button" wire:click="togglePayments" class="text-sm font-semibold text-cyan-300 hover:text-cyan-200">
                    {{ $showAllPayments ? 'Show fewer' : 'Show all (' . $historyTotal . ')' }}
                </button>
                @endif
            </div>

            @if (empty($payments))
            <p class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4 text-sm text-slate-300">
                No payments yet. When you upgrade, your payments will appear here.
            </p>
            @else
            <div class="overflow-x-auto rounded-2xl border border-white/10 bg-white text-slate-900 shadow-lg">
                <table class="min-w-full text-left text-sm">
                    <caption class="sr-only">Your payments, newest first</caption>
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">Date</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Plan</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Amount</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                            <th scope="col" class="px-4 py-3 font-semibold">Reference</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($payments as $payment)
                        <tr wire:key="payment-{{ $payment['id'] }}">
                            <td class="whitespace-nowrap px-4 py-3">{{ $payment['date'] }}</td>
                            <td class="px-4 py-3">{{ $payment['description'] }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-semibold">{{ $payment['amount'] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusStyles[$payment['status']] ?? 'bg-slate-200 text-slate-700' }}">
                                    {{ $statusLabels[$payment['status']] ?? 'Closed' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $payment['reference'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-slate-400">Quote the payment reference if you ever need help with a payment.</p>
            @endif
        </section>
    </div>
</x-parent-portal.layout.app-shell>