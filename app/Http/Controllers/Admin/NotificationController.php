<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Jobs\SendPushNotification;

class NotificationController extends Controller
{
    /**
     * Show the form for creating a new notification.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.notifications.create');
    }

    /**
     * Send the broadcast notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
        ]);

        $title = $request->title;
        $body = $request->body;

        // Fetch users in chunks to avoid memory issues with large numbers of users
        User::whereNotNull('fcm_token')->chunk(200, function ($users) use ($title, $body) {
            foreach ($users as $user) {
                SendPushNotification::dispatch($user, $title, $body);
            }
        });

        return redirect()->route('admin.notifications.create')->with('success', 'Broadcast notification has been queued for sending!');
    }
}
