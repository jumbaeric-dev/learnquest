<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NovaMessage extends Model
{
    protected $fillable = [
        'child_id',
        'role',
        'content',
        'provider',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
