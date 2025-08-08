<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_id', 'title', 'description', 'video_url', 'duration_minutes',
        'order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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
}