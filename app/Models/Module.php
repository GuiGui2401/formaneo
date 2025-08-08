<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['formation_id','title','duration','video_url','order','metadata'];

    protected $casts = ['metadata'=>'array'];

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }
}
