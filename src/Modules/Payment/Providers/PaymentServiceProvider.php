<?php

namespace App\Modules\Payment\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Payment\Providers\PaymentEventServiceProvider;

/*
 * Payment service provider
 */
class PaymentServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Payment';

    /**
     * Module name
     */
    protected string $moduleName = 'Payment';

    /**
     * Services
     */
    protected array $services = [
        PaymentService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'payment',
    ];


    public function boot(): void
    {
        parent::boot();
        //register events
        $this->app->register(PaymentEventServiceProvider::class);
    }
}
