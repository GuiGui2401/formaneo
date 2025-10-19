<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class FormationCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'formation_id',
        'certificate_number',
        'issued_at',
        'certificate_url',
        'metadata'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formation()
    {
        return $this->belongsTo(Formation::class);
    }

    // Générer un numéro de certificat unique
    public static function generateCertificateNumber()
    {
        return 'CERT-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');
    }
}
