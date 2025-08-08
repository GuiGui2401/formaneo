<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'formation_id', 'progress', 'completed_at', 'cashback_claimed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'cashback_claimed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
}