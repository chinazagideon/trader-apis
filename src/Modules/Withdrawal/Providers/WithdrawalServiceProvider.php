<?php

namespace App\Modules\Withdrawal\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use Illuminate\Support\Facades\Route;

class WithdrawalServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Withdrawal';

     /**
     * Services
     */
    protected array $services = [
        'WithdrawalService'::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'withdrawal',
    ];
}