<?php

namespace App\Modules\Payment\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Payment\Services\PaymentService;
use App\Modules\Payment\Providers\PaymentEventServiceProvider;
use App\Modules\Payment\Services\PaymentGatewayService;
use App\Modules\Payment\Contracts\PaymentGatewayServiceContract;
use App\Modules\Payment\Contracts\PaymentProcessorServiceContract;
use App\Modules\Payment\Services\PaymentProcessorService;


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


    /**
     * Bootstrap the service provider.
     */
    public function boot(): void
    {
        parent::boot();
        //register events
        $this->app->register(PaymentEventServiceProvider::class);
    }

    /**
     * Register services
     */
    protected function registerServices(): void
    {
        $this->app->bind(PaymentGatewayServiceContract::class, PaymentGatewayService::class);
        $this->app->bind(PaymentProcessorServiceContract::class, PaymentProcessorService::class);
    }
}
