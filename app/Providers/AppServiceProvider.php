<?php

namespace App\Providers;

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
        // Auto-migrate on Vercel if connecting to remote Postgres and tables don't exist yet
        if (isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL'])) {
            try {
                if (! \Illuminate\Support\Facades\Schema::hasTable('users')) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                }
            } catch (\Throwable $e) {
                // Ignore if DB is unreachable or during initial build
            }
        }
    }
}
