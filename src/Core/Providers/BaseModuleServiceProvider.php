<?php

namespace App\Core\Providers;

use App\Core\Contracts\ModuleServiceProviderInterface;
use Illuminate\Support\ServiceProvider;

abstract class BaseModuleServiceProvider extends ServiceProvider implements ModuleServiceProviderInterface
{
    protected ?string $modulePath = null;

    // Override only when you need custom names; by default infer from namespace
    public function getModuleName(): string
    {
        // Expecting: App\Modules\{Module}\Providers\{...}
        $ns = static::class;
        if (preg_match('/^App\\\\Modules\\\\([^\\\\]+)/', $ns, $m)) {
            return $m[1];
        }
        throw new \RuntimeException('Cannot infer module name from provider namespace: ' . $ns);
    }

    public function getModuleNamespace(): string
    {
        return 'App\\Modules\\' . $this->getModuleName();
    }

    public function getModulePath(): string
    {
        if ($this->modulePath === null) {
            $this->modulePath = base_path('src/Modules/' . $this->getModuleName());
        }
        return $this->modulePath;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function shouldRegister(): bool
    {
        return true;
    }

    // Child modules override to bind concrete services
    protected function registerServices(): void {}

    public function register(): void
    {
        $this->registerServices();
    }

    public function boot(): void
    {
        // Intentionally empty; ModuleServiceProvider handles
        // loading routes/config/views/translations/migrations.
    }
}
