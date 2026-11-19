<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Auto-copy .env & buat database.sqlite di komputer baru (Windows/Mac) jika belum ada
        $basePath = base_path();
        $envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
        $envExample = $basePath . DIRECTORY_SEPARATOR . '.env.example';
        if (! is_file($envFile) && is_file($envExample)) {
            @copy($envExample, $envFile);
        }

        $sqliteFile = database_path('database.sqlite');
        if (! is_file($sqliteFile) && is_dir(dirname($sqliteFile))) {
            @touch($sqliteFile);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto-migrate & auto-seed di mode lokal (bukan saat phpunit testing) jika database baru masih kosong
        if (! $this->app->runningUnitTests() && ! $this->app->runningInConsole()) {
            try {
                if (! Schema::hasTable('users') || User::count() === 0) {
                    Artisan::call('migrate', ['--force' => true]);
                    Artisan::call('db:seed', ['--force' => true]);
                } elseif (Schema::hasTable('service_bookings') && ! Schema::hasColumn('service_bookings', 'variant_id')) {
                    Artisan::call('migrate', ['--force' => true]);
                }
            } catch (\Throwable $e) {
                // Abaikan jika koneksi DB belum siap
            }
        }
    }
}
