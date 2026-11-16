<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionReminder extends Model
{
    protected $fillable = [
        'subscription_id',
        'kind',
        'period_ends_at',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'period_ends_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}