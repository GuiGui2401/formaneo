<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['title','description','questions_count','passing_score','reward_per_correct','metadata'];

    protected $casts = ['metadata'=>'array'];
}
