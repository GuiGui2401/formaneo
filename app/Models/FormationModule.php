<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id', 'title', 'content', 'video_url', 'duration_minutes',
        'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
}