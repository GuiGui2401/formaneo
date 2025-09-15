<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'pack_id', 'price_paid', 'purchased_at'
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'purchased_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pack()
    {
        return $this->belongsTo(FormationPack::class, 'pack_id');
    }
}