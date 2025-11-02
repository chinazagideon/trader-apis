<?php

namespace App\Core\Resolvers;

class OperationClassResolver
{
    /**
     * Resolve a custom FormRequest class by convention
     * Pattern priority:
     * 1) App\Modules\{Module}\Http\Requests\{Module}{SubResource}{Operation}Request
     * 2) App\Modules\{Module}\Http\Requests\{SubResource}{Operation}Request
     * 3) App\Modules\{Module}\Http\Requests\{Module}{Operation}Request
     */
    public function resolveRequest(string $module, ?string $subResource, string $operation): ?string
    {
        $strategies = $this->buildStrategies('Requests', $module, $subResource, $operation, 'Request');
        foreach ($strategies as $fqcn) {
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }
        return null;
    }

    /**
     * Resolve a custom JsonResource class by convention
     * Pattern priority:
     * 1) App\Modules\{Module}\Http\Resources\{Module}{SubResource}{Operation}Resource
     * 2) App\Modules\{Module}\Http\Resources\{SubResource}{Operation}Resource
     * 3) App\Modules\{Module}\Http\Resources\{Module}{Operation}Resource
     */
    public function resolveResource(string $module, ?string $subResource, string $operation): ?string
    {
        $strategies = $this->buildStrategies('Resources', $module, $subResource, $operation, 'Resource');
        foreach ($strategies as $fqcn) {
            if (class_exists($fqcn)) {
                return $fqcn;
            }
        }
        return null;
    }

    /**
     * Build resolution strategies respecting config-driven fallbacks
     */
    private function buildStrategies(string $category, string $module, ?string $subResource, string $operation, string $suffix): array
    {
        $config = config('app.enhanced_resolution', []);
        $strategies = [];

        if ($subResource) {
            if ($config['fallback_strategies']['sub_resource_specific'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\{$category}\\{$module}{$subResource}{$operation}{$suffix}";
            }
            if ($config['fallback_strategies']['sub_resource_conventional'] ?? true) {
                $strategies[] = "App\\Modules\\{$module}\\Http\\{$category}\\{$subResource}{$operation}{$suffix}";
            }
        }

        if ($config['fallback_strategies']['module_specific'] ?? true) {
            $strategies[] = "App\\Modules\\{$module}\\Http\\{$category}\\{$module}{$operation}{$suffix}";
        }

        return $strategies;
    }
}


