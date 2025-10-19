<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','slug','author','description','thumbnail_url','price','cashback_amount','total_duration',
        'rating','students_count','is_active','is_featured','order','metadata',
        'is_on_promotion','promotion_price','promotion_starts_at','promotion_ends_at'
    ];

    protected $casts = [
        'metadata'=>'array',
        'is_active'=>'boolean',
        'is_featured'=>'boolean',
        'is_on_promotion'=>'boolean',
        'promotion_starts_at'=>'datetime',
        'promotion_ends_at'=>'datetime'
    ];

    public function formations()
    {
        return $this->hasMany(Formation::class,'pack_id');
    }

    public function packProgress()
    {
        return $this->hasMany(FormationPackProgress::class, 'pack_id');
    }

    /**
     * Check if the pack is currently on promotion
     */
    public function isPromotionActive()
    {
        if (!$this->is_on_promotion) {
            return false;
        }

        $now = now();
        
        if ($this->promotion_starts_at && $now->lt($this->promotion_starts_at)) {
            return false;
        }
        
        if ($this->promotion_ends_at && $now->gt($this->promotion_ends_at)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the current effective price (promotion price if active, otherwise regular price)
     */
    public function getCurrentPrice()
    {
        return $this->isPromotionActive() ? $this->promotion_price : $this->price;
    }
}
