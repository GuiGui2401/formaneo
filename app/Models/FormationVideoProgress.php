<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationVideoProgress extends Model
{
    use HasFactory;

    protected $table = 'formation_video_progress';

    protected $fillable = [
        'user_id',
        'formation_video_id',
        'progress',
        'completed_at'
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(FormationVideo::class, 'formation_video_id');
    }

    // Marquer comme complété
    public function markAsCompleted()
    {
        $this->update([
            'progress' => 100,
            'completed_at' => now()
        ]);
    }
}
