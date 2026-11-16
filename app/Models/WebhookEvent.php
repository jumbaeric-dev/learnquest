<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookEvent extends Model
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_DUPLICATE = 'duplicate';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'payment_id', 'provider', 'event_id', 'event_type', 'payment_reference',
        'status', 'signature_verified_at', 'payload', 'attempts', 'processed_at', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'signature_verified_at' => 'datetime',
            'payload' => 'encrypted:array',
            'attempts' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
