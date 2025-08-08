<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Formation extends Model
{
    use HasFactory;

    protected $fillable = ['pack_id','title','description','duration','video_url','order','metadata'];

    protected $casts = ['metadata'=>'array'];

    public function pack()
    {
        return $this->belongsTo(FormationPack::class,'pack_id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}
