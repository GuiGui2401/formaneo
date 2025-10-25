<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging;
use Throwable;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $title;
    protected $body;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, string $title, string $body)
    {
        $this->user = $user;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Messaging $messaging)
    {
        if (empty($this->user->fcm_token)) {
            return;
        }

        $message = CloudMessage::withTarget('token', $this->user->fcm_token)
            ->withNotification(Notification::create($this->title, $this->body));

        try {
            $messaging->send($message);
        } catch (Throwable $e) {
            // Handle failed send, e.g., if token is invalid
            // Log the error
            report($e);
        }
    }
}
