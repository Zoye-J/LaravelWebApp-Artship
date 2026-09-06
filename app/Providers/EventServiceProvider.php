<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen(Registered::class, function ($event) {
            $user = $event->user;

        // Assign admin role ONLY to exact @admin.com domain
            if (str_ends_with($user->email, '@admin.com')) {
                $user->role = 'admin';
            } else {
                $user->role = 'user';
            }

            $user->save();
        });
    }

   
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
