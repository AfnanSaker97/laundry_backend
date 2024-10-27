<?php

namespace App\Providers;
use App\Domain\Orders\Events\TestingEvent;
use App\Domain\Orders\Listeners\LocationUpdateListener;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
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
        Event::listen(
            TestingEvent::class,
            LocationUpdateListener::class,
        );
    }
}
