<?php

namespace App\Core\Http\Controllers;

use App\Core\Controllers\BaseController;
use App\Core\ModuleManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiGatewayController extends BaseController
{
    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get API Gateway status
     */
    public function status(): JsonResponse
    {
        $modules = $this->moduleManager->getAllModulesHealth();
        $registeredModules = $this->moduleManager->getRegisteredModules();

        $healthyModules = collect($modules)->filter(fn($health) => $health['status'] === 'healthy')->count();
        $totalModules = count($modules);

        return $this->successResponse([
            'gateway_status' => 'operational',
            'modules' => [
                'total' => $totalModules,
                'registered' => count($registeredModules),
                'healthy' => $healthyModules,
                'unhealthy' => $totalModules - $healthyModules,
            ],
            'module_health' => $modules,
            'registered_modules' => $registeredModules,
        ], 'API Gateway status retrieved successfully');
    }

    /**
     * Get module information
     */
    public function moduleInfo(string $module): JsonResponse
    {
        $moduleData = $this->moduleManager->getModule($module);

        if (!$moduleData) {
            return $this->notFoundResponse("Module '{$module}' not found");
        }

        $health = $this->moduleManager->getModuleHealth($module);

        return $this->successResponse([
            'module' => $moduleData,
            'health' => $health,
            'is_registered' => $this->moduleManager->isModuleRegistered($module),
        ], 'Module information retrieved successfully');
    }

    /**
     * Get all modules information
     */
    public function modulesInfo(): JsonResponse
    {
        $modules = $this->moduleManager->getModules();
        $modulesWithHealth = [];

        foreach ($modules as $name => $module) {
            $modulesWithHealth[$name] = [
                'module' => $module,
                'health' => $this->moduleManager->getModuleHealth($name),
                'is_registered' => $this->moduleManager->isModuleRegistered($name),
            ];
        }

        return $this->successResponse($modulesWithHealth, 'All modules information retrieved successfully');
    }

    /**
     * Register a module
     */
    public function registerModule(string $module): JsonResponse
    {
        $success = $this->moduleManager->registerModule($module);

        if (!$success) {
            return $this->errorResponse("Failed to register module '{$module}'", null, 400);
        }

        return $this->successResponse([
            'module' => $module,
            'status' => 'registered',
        ], "Module '{$module}' registered successfully");
    }

    /**
     * Health check endpoint
     */
    public function health(): JsonResponse
    {
        $modules = $this->moduleManager->getAllModulesHealth();
        $allHealthy = collect($modules)->every(fn($health) => $health['status'] === 'healthy');

        $status = $allHealthy ? 'healthy' : 'degraded';
        $statusCode = $allHealthy ? 200 : 503;

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'modules' => $modules,
        ], $statusCode);
    }
}
