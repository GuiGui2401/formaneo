<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestNotification extends Notification
{
    use Queueable;

    protected $transaction;
    protected $user;

    public function __construct($transaction, $user)
    {
        $this->transaction = $transaction;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        // Dans un vrai système, vous pourriez utiliser différentes canaux comme:
        // return ['mail', 'database', 'slack'];
        // Pour l'instant, nous allons utiliser la base de données
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $meta = json_decode($this->transaction->meta, true);
        $phoneNumber = $meta['phone_number'] ?? 'N/A';
        $method = $meta['method'] ?? 'N/A';

        return [
            'transaction_id' => $this->transaction->id,
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'amount' => $this->transaction->amount,
            'phone_number' => $phoneNumber,
            'method' => $method,
            'created_at' => now(),
        ];
    }

    public function toArray($notifiable)
    {
        return $this->toDatabase($notifiable);
    }
}