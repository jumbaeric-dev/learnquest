<?php

$payments = App\Models\Payment::where('provider', 'intasend')->latest('id')->take(10)->get();

$payments->map(fn ($p) => [
    'id' => $p->id,
    'reference' => $p->reference,
    'status' => $p->status,
    'amount' => $p->amount_minor / 100,
    'currency' => $p->currency,
    'invoice' => data_get($p->metadata, 'intasend_invoice_id'),
    'checkout_url' => filled(data_get($p->metadata, 'intasend_checkout_url')),
    'initiation_started' => data_get($p->metadata, 'intasend_checkout_initiation_started_at'),
])->all();