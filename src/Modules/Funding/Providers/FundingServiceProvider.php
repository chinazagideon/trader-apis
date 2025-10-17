<?php

namespace App\Modules\Funding\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;

class FundingServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Funding';

     /**
     * Services
     */
    protected array $services = [
        'FundingService'::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'funding',
    ];
}