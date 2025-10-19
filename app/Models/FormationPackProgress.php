<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FormationPackProgress extends Model
{
    use HasFactory;

    protected $table = 'formation_pack_progress';

    protected $fillable = [
        'user_id',
        'pack_id',
        'progress',
        'completed_at',
        'cashback_claimed_at'
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'completed_at' => 'datetime',
        'cashback_claimed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pack()
    {
        return $this->belongsTo(FormationPack::class, 'pack_id');
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
