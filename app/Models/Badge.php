<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'xp_reward',
        'is_active',
    ];

    public function __toString(): string
    {
        return $this->name;
    }

    public function children()
    {
        return $this->belongsToMany(Child::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }
}
