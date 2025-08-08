<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationPack extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','slug','author','description','thumbnail_url','price','total_duration',
        'rating','students_count','is_active','is_featured','order','metadata'
    ];

    protected $casts = [
        'metadata'=>'array',
        'is_active'=>'boolean',
        'is_featured'=>'boolean'
    ];

    public function formations()
    {
        return $this->hasMany(Formation::class,'pack_id');
    }
}
