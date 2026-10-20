<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
        if (getenv('VERCEL') || isset($_SERVER['VERCEL']) || isset($_ENV['VERCEL'])) {
            $flag = '/tmp/.migrated';
            if (! file_exists($flag)) {
                try {
                    if (! Schema::hasTable('users')) {
                        Artisan::call('migrate', ['--force' => true]);
                        Artisan::call('db:seed', ['--force' => true]);
                    }
                    @touch($flag);
                } catch (\Throwable $e) {
                    Log::error('Auto-migration error: '.$e->getMessage());
                }
            }
        }
    }
}
