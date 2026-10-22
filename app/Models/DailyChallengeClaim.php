<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyChallengeClaim extends Model
{
    protected $fillable = [
        'child_id',
        'challenge_key',
        'challenge_date',
        'xp_awarded',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}