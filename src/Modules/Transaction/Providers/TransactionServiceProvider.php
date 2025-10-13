<?php

namespace App\Modules\Transaction\Providers;

use App\Core\Providers\ModuleServiceProviderBase;
use App\Modules\Transaction\Services\TransactionService;
use App\Modules\Transaction\Services\TransactionCategoryService;

class TransactionServiceProvider extends ModuleServiceProviderBase
{
    public function register(): void
    {
        $this->registerModuleConfig();
        $this->registerModuleMigrations();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->registerModuleViews();
        $this->registerModuleTranslations();
    }

    protected function registerServices(): void
    {
        $this->app->bind(TransactionService::class, TransactionService::class);
        $this->app->bind(TransactionCategoryService::class, TransactionCategoryService::class);
    }
}
