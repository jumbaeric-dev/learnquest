<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildSkillProgress extends Model
{
    protected $fillable = [
        'child_id',
        'skill_id',
        'xp',
        'level',
        'progress_percentage',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}