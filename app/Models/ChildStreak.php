<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildStreak extends Model
{
    protected $fillable = [
        'child_id',
        'current_streak',
        'longest_streak',
        'last_activity_date',
    ];

    protected $casts = [
        'last_activity_date' => 'date',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}