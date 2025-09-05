<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_id', 'title', 'description', 'video_url', 'thumbnail_url', 'duration_minutes',
        'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'order' => 'integer',
    ];

    public function pack()
    {
        return $this->belongsTo(FormationPack::class, 'pack_id');
    }

    public function modules()
    {
        return $this->hasMany(FormationModule::class);
    }

    public function progress()
    {
        return $this->hasMany(FormationProgress::class);
    }

    // Accesseur pour l'URL de la miniature
    public function getThumbnailUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Retourner une image par défaut si aucune n'est définie
        return config('app.url') . '/images/formation-default-thumbnail.png';
    }
}