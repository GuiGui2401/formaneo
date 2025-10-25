<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'amount', 'description', 'status', 'meta', 'completed_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'amount' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    protected $appends = ['is_credit', 'product'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProductAttribute()
    {
        $meta = is_string($this->meta) ? json_decode($this->meta, true) : $this->meta;

        if (!is_array($meta)) {
            return null;
        }

        if (array_key_exists('product_id', $meta)) {
            $productId = $meta['product_id'];
            return Product::find($productId);
        }
        return null;
    }

    public function getIsCreditAttribute()
    {
        return $this->amount > 0;
    }
}
