<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Money;

class PlanPrice extends Model
{
    protected $fillable = [
        'plan_id',
        'currency',
        'months',
        'amount_minor',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'months' => 'integer',
            'amount_minor' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** "KSh 499" or "$4.99". */
    /** "KSh 499" or "$4.99". */
    public function formatted(): string
    {
        return Money::format($this->amount_minor, $this->currency);
    }

    public function label(): string
    {
        return match ($this->months) {
            1 => 'Monthly',
            3 => '3 months',
            12 => 'Yearly',
            default => "{$this->months} months",
        };
    }
}