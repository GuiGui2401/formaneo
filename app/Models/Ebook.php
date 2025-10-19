<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Ebook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image_url',
        'pdf_url',
        'author',
        'price',
        'pages',
        'category',
        'rating',
        'downloads',
        'is_active',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'downloads' => 'integer',
        'pages' => 'integer',
    ];

    protected $appends = ['is_free'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ebook) {
            if (empty($ebook->slug)) {
                $ebook->slug = Str::slug($ebook->title);
            }
        });

        static::updating(function ($ebook) {
            if (empty($ebook->slug)) {
                $ebook->slug = Str::slug($ebook->title);
            }
        });
    }

    // Scope pour les ebooks actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accesseur pour l'URL de couverture
    public function getCoverImageUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Retourner une image par défaut si aucune n'est définie
        return config('app.url') . '/images/ebook-default-cover.png';
    }

    // Accesseur pour l'URL du PDF
    public function getPdfUrlAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        return null;
    }

    // Relations
    public function purchases()
    {
        return $this->hasMany(UserEbook::class);
    }

    // Accesseurs
    public function getFormattedPriceAttribute()
    {
        if ($this->price > 0) {
            return number_format($this->price, 0, ',', ' ') . ' FCFA';
        }
        return 'Gratuit';
    }

    public function getIsFreeAttribute()
    {
        return $this->price <= 0;
    }
}