<?php

namespace App\Providers;

use App\Events\OrderMatched;
use App\Listeners\LogOrderMatched;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
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
        // Fix for MySQL key length issue with utf8mb4
        Schema::defaultStringLength(191);

        Vite::prefetch(concurrency: 3);

        // Register event listeners
        Event::listen(OrderMatched::class, LogOrderMatched::class);
    }
}
