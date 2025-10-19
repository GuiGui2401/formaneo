<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionalBanner extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'width',
        'height',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}
