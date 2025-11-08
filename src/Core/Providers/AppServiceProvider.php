<?php

namespace App\Core\Providers;

use App\Core\Services\LoggingService;
use App\Core\Services\EventDispatcher;
use App\Core\Services\TransactionContextFactory;
use App\Core\Providers\BaseModuleServiceProvider;

class AppServiceProvider extends BaseModuleServiceProvider
{
    public function register(): void
    {

    }
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
         // Register the LoggingService as singleton
         $this->app->singleton(LoggingService::class, function ($app) {
            return new LoggingService();
        });

        // Register the EventDispatcher as singleton
        $this->app->singleton(EventDispatcher::class, function ($app) {
            return new EventDispatcher();
        });

        // Register the TransactionContextFactory as singleton
        $this->app->singleton(TransactionContextFactory::class, function ($app) {
            return new TransactionContextFactory($app->make(LoggingService::class));
        });
    }

}
