<?php

namespace App\Modules\Transaction\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Transaction\Services\TransactionService;
use App\Modules\Transaction\Services\TransactionCategoryService;

class TransactionServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Transaction';

    /**
     * Module name
     */
    protected string $moduleName = 'Transaction';

    /**
     * Services
     */
    protected array $services = [
        TransactionService::class,
        TransactionCategoryService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'transaction',
    ];

    protected function registerServices(): void
    {
        $this->app->bind(TransactionService::class, TransactionService::class);
        $this->app->bind(TransactionCategoryService::class, TransactionCategoryService::class);
    }
}
