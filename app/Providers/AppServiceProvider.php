<?php

namespace App\Providers;

use App\Contracts\SandboxExecutor;
use App\Services\Sandbox\DaytonaSandboxExecutor;
use App\Services\Sandbox\UnavailableSandboxExecutor;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SandboxExecutor::class, function ($app) {
            return config('sandbox.driver') === 'daytona'
                ? $app->make(DaytonaSandboxExecutor::class)
                : $app->make(UnavailableSandboxExecutor::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
