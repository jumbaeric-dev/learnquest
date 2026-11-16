<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Append-only audit record of complimentary time. */
class SubscriptionGrant extends Model
{
    protected $fillable = [
        'subscription_id',
        'user_id',
        'granted_by',
        'plan_id',
        'days',
        'previous_ends_at',
        'new_ends_at',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'integer',
            'previous_ends_at' => 'datetime',
            'new_ends_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}