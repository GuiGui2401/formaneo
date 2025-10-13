<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challenge extends Model
{
    protected $fillable = [
        'title',
        'description',
        'reward',
        'image_url',
        'icon_name',
        'target',
        'expires_at',
        'is_active',
        'order',
    ];

    protected $casts = [
        'reward' => 'decimal:2',
        'target' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_challenges')
            ->withPivot('progress', 'is_completed', 'completed_at', 'reward_claimed')
            ->withTimestamps();
    }

    /**
     * Scope pour les défis actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les défis non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
