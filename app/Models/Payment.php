<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    protected $fillable = [
        'user_id', 'subscription_id', 'plan_id', 'plan_price_id', 'plan_key',
        'duration_months', 'reference', 'idempotency_key', 'provider',
        'provider_transaction_id', 'amount_minor', 'currency', 'status',
        'expires_at', 'paid_at', 'failed_at', 'failure_code', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'duration_months' => 'integer',
            'amount_minor' => 'integer',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(Subscription::class); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function price(): BelongsTo { return $this->belongsTo(PlanPrice::class, 'plan_price_id'); }
    public function webhookEvents(): HasMany { return $this->hasMany(WebhookEvent::class); }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function scopeSucceeded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCEEDED);
    }

    /** Unsettled for over an hour: worth a look. */
    public function scopeStuck(Builder $query): Builder
    {
        return $query->pending()->where('created_at', '<', now()->subHour());
    }

    /** Settled after the checkout window closed, or after being marked failed/cancelled. */
    public function scopeLateSettlements(Builder $query): Builder
    {
        return $query->where('metadata->late_settlement', true);
    }

    public function formattedAmount(): string
    {
        return Money::format($this->amount_minor, $this->currency);
    }

    public function periodLabel(): string
    {
        return match ($this->duration_months) {
            1 => 'Monthly',
            3 => '3 months',
            12 => 'Yearly',
            default => "{$this->duration_months} months",
        };
    }

    public function isLateSettlement(): bool
    {
        return (bool) data_get($this->metadata, 'late_settlement', false);
    }
}