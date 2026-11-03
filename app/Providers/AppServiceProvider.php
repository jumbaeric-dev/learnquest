<?php

namespace App\Providers;

use App\Contracts\Ai\NovaAiProviderContract;
use App\Services\Nova\NovaProviderFactory;
use Illuminate\Support\ServiceProvider;
use App\Services\Billing\EntitlementService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            NovaAiProviderContract::class,
            fn($app) => $app->make(NovaProviderFactory::class)->make()
        );
        
        $this->app->singleton(EntitlementService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
