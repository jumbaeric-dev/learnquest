<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public function payments()
    {
        return $this->hasMany(Payment::class);
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

    public function grants(): HasMany
    {
        return $this->hasMany(SubscriptionGrant::class);
    }

    /** Grants access right now (same rule as the current() scope). */
    public function isCurrent(): bool
    {
        return in_array($this->status, [
            self::STATUS_TRIALING,
            self::STATUS_ACTIVE,
            self::STATUS_CANCELED,
        ], true)
            && $this->starts_at->lte(now())
            && $this->ends_at->gt(now());
    }

    public function isComplimentary(): bool
    {
        return $this->gateway === 'complimentary';
    }

    /** Days a lapsed paid plan keeps working. */
    public static function graceDays(): int
    {
        return max(0, (int) config('learnquest.billing.grace_days', 2));
    }

    /** When grace ends, or null if this subscription never gets grace. */
    public function graceEndsAt(): ?Carbon
    {
        $days = self::graceDays();

        if (
            $days === 0
            || $this->isTrial()
            || $this->isComplimentary()
            || ! in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_CANCELED], true)
        ) {
            return null;
        }

        return $this->ends_at->copy()->addDays($days);
    }

    /** Ended, but still inside its grace period. */
    public function inGrace(): bool
    {
        $graceEnds = $this->graceEndsAt();

        return $graceEnds !== null
            && $this->starts_at->lte(now())
            && $this->ends_at->lte(now())
            && $graceEnds->gt(now());
    }

    public function scopeInGrace(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_CANCELED])
            ->where(fn(Builder $inner) => $inner
                ->whereNull('gateway')
                ->orWhere('gateway', '!=', 'complimentary'))
            ->where('starts_at', '<=', now())
            ->where('ends_at', '<=', now())
            ->where('ends_at', '>', now()->subDays(self::graceDays()));
    }

    public function stateLabel(): string
    {
        return match (true) {
            $this->inGrace() => 'Grace period',
            !$this->isCurrent() => 'Expired',
            $this->isTrial() => 'Trial',
            $this->isComplimentary() => 'Complimentary',
            default => 'Active',
        };
    }
}
