<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserEbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ebook_id',
        'price_paid',
        'purchased_at',
        'downloaded_at',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'price_paid' => 'decimal:2',
        'purchased_at' => 'datetime',
        'downloaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ebook()
    {
        return $this->belongsTo(Ebook::class);
    }
}