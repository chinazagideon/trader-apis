<?php

namespace App\Modules\Category\Providers;

use App\Core\Providers\BaseModuleServiceProvider;
use App\Modules\Category\Services\CategoryService;
use Illuminate\Support\Facades\Route;

class CategoryServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Module namespace
     */
    protected string $moduleNamespace = 'App\\Modules\\Category';

    /**
     * Module name
     */
    protected string $moduleName = 'Category';

    /**
     * Services
     */
    protected array $services = [
        CategoryService::class,
    ];

    /**
     * Config files
     */
    protected array $configFiles = [
        'category',
    ];

}
