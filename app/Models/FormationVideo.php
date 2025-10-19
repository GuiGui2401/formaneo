<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'formation_id',
        'title',
        'description',
        'video_url',
        'duration_minutes',
        'order',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'order' => 'integer',
        'metadata' => 'array',
    ];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    public function progress()
    {
        return $this->hasMany(FormationVideoProgress::class);
    }

    // Scope pour les vidéos actives
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
