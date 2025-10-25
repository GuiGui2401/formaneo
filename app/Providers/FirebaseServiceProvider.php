<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Messaging::class, function ($app) {
            $credentials = config('firebase.credentials');

            if (empty($credentials)) {
                // Return a null object or a mock if credentials are not set
                // This prevents crashes in environments without firebase configured.
                return new class {
                    public function send($message): void {}
                    public function sendMulticast($message, $deviceTokens): void {}
                    // Add other methods as needed to avoid errors
                };
            }

            $factory = (new Factory)->withServiceAccount($credentials);
            return $factory->createMessaging();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
