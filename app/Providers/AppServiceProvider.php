<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // Self-healing database check
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable('products')) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true, '--seed' => true]);
                }
            } catch (\Exception $e) {
                // Silently fail or log if needed
            }
        }
    }
}
