<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'title', 'message', 'data', 'is_read', 'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public static function createNewUserNotification($user)
    {
        return self::create([
            'type' => 'new_user_registration',
            'title' => 'Nouvel utilisateur inscrit',
            'message' => "Un nouvel utilisateur s'est inscrit: {$user->name} ({$user->email})",
            'data' => [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'account_status' => $user->account_status,
                'platform' => 'web'
            ]
        ]);
    }

    public static function createActivationNeededNotification($user)
    {
        return self::create([
            'type' => 'account_activation_needed',
            'title' => 'Activation de compte requise',
            'message' => "L'utilisateur {$user->name} doit activer son compte",
            'data' => [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'account_status' => $user->account_status
            ]
        ]);
    }

    public static function createPaymentReceivedNotification($transaction)
    {
        return self::create([
            'type' => 'payment_received',
            'title' => 'Paiement d\'activation reçu',
            'message' => "Paiement de {$transaction->amount} FCFA reçu pour l'activation du compte",
            'data' => [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount,
                'transaction_type' => $transaction->type
            ]
        ]);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
