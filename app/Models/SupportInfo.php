<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportInfo extends Model
{
    protected $fillable = [
        'type',
        'label',
        'value',
        'description',
        'icon_name',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Scope pour les infos actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc');
    }
}
