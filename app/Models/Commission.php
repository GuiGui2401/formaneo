<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','amount','source_type','source_id','paid'];

    protected $casts = ['paid'=>'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
