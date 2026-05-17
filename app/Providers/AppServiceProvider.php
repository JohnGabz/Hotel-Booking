<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\PaymentVerified;
use App\Listeners\QueueBookingLifecycleEmail;
use App\Listeners\SyncBookingLifecycleData;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BookingCreated::class, SyncBookingLifecycleData::class);
        Event::listen(BookingCreated::class, QueueBookingLifecycleEmail::class);
        Event::listen(PaymentVerified::class, SyncBookingLifecycleData::class);
        Event::listen(BookingConfirmed::class, SyncBookingLifecycleData::class);
        Event::listen(BookingConfirmed::class, QueueBookingLifecycleEmail::class);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
