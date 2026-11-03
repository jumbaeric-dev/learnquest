<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public const STATUS_TRIALING = 'trialing';

    public const STATUS_ACTIVE = 'active';

    /** Auto-renew switched off; access continues until ends_at. */
    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'user_id',
        'plan_id',
        'plan_price_id',
        'status',
        'starts_at',
        'ends_at',
        'canceled_at',
        'auto_renews',
        'gateway',
        'gateway_reference',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'canceled_at' => 'datetime',
            'auto_renews' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(PlanPrice::class, 'plan_price_id');
    }

    /** Subscriptions that grant access right now. */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                self::STATUS_TRIALING,
                self::STATUS_ACTIVE,
                self::STATUS_CANCELED,
            ])
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now());
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIALING;
    }
}