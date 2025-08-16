<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description', 
        'questions',
        'difficulty',
        'subject',
        'questions_count',
        'is_active'
    ];

    protected $casts = [
        'questions' => 'array', // IMPORTANT: Cast le JSON en array
        'is_active' => 'boolean'
    ];

    // Accesseur pour s'assurer que questions est toujours un array
    public function getQuestionsAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        
        return $value ?: [];
    }

    // Mutateur pour s'assurer que questions est sauvé en JSON
    public function setQuestionsAttribute($value)
    {
        $this->attributes['questions'] = is_array($value) ? json_encode($value) : $value;
    }
}