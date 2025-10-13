<?php

namespace App\Core\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * Enhanced Logging Service with structured logging and performance tracking
 */
class LoggingService
{
    protected array $logSwitches = [];
    protected array $performanceThresholds = [];

    public function __construct()
    {
        $this->loadLogSwitches();
        $this->loadPerformanceThresholds();
    }

    /**
     * Load log switches from configuration
     */
    protected function loadLogSwitches(): void
    {
        $this->logSwitches = [
            'operation_lifecycle' => config('logging.switches.operation_lifecycle', true),
            'performance_metrics' => config('logging.switches.performance_metrics', true),
            'request_context' => config('logging.switches.request_context', true),
            'business_logic' => config('logging.switches.business_logic', true),
            'repository_queries' => config('logging.switches.repository_queries', false),
            'detailed_errors' => config('logging.switches.detailed_errors', true),
            'memory_tracking' => config('logging.switches.memory_tracking', false),
        ];
    }

    /**
     * Load performance thresholds
     */
    protected function loadPerformanceThresholds(): void
    {
        $this->performanceThresholds = [
            'slow_query_ms' => config('logging.thresholds.slow_query_ms', 1000),
            'slow_operation_ms' => config('logging.thresholds.slow_operation_ms', 2000),
            'memory_warning_mb' => config('logging.thresholds.memory_warning_mb', 128),
        ];
    }

    /**
     * Log operation lifecycle events
     */
    public function logOperation(string $service, string $operation, string $phase, string $message, array $context = []): void
    {
        if (!$this->logSwitches['operation_lifecycle']) {
            return;
        }

        $context = $this->enrichContext($context, [
            'service' => $service,
            'operation' => $operation,
            'phase' => $phase,
        ]);

        Log::info("[{$service}] {$operation} - {$phase}: {$message}", $context);
    }

    /**
     * Log performance metrics
     */
    public function logPerformance(string $service, string $operation, float $durationMs, array $context = []): void
    {
        if (!$this->logSwitches['performance_metrics']) {
            return;
        }

        $context = $this->enrichContext($context, [
            'service' => $service,
            'operation' => $operation,
            'duration_ms' => round($durationMs, 2),
            'is_slow' => $durationMs > $this->performanceThresholds['slow_operation_ms'],
        ]);

        $level = $durationMs > $this->performanceThresholds['slow_operation_ms'] ? 'warning' : 'info';
        Log::log($level, "[{$service}] Performance: {$operation}", $context);
    }

    /**
     * Log business logic events
     */
    public function logBusinessLogic(string $service, string $operation, string $message, array $context = []): void
    {
        if (!$this->logSwitches['business_logic']) {
            return;
        }

        $context = $this->enrichContext($context, [
            'service' => $service,
            'operation' => $operation,
        ]);

        Log::info("[{$service}] Business Logic: {$message}", $context);
    }

    /**
     * Log repository/database operations
     */
    public function logRepository(string $repository, string $operation, string $message, array $context = []): void
    {
        if (!$this->logSwitches['repository_queries']) {
            return;
        }

        $context = $this->enrichContext($context, [
            'repository' => $repository,
            'operation' => $operation,
        ]);

        Log::info("[{$repository}] Database: {$message}", $context);
    }

    /**
     * Log errors with enhanced context
     */
    public function logError(string $service, string $operation, \Exception $exception, array $context = []): void
    {
        $context = $this->enrichContext($context, [
            'service' => $service,
            'operation' => $operation,
            'exception_class' => get_class($exception),
            'error_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        if ($this->logSwitches['detailed_errors']) {
            $context = array_merge($context, [
                'stack_trace' => $exception->getTraceAsString(),
                'request_data' => request()->all(),
                'request_headers' => request()->headers->all(),
            ]);
        }

        Log::error("[{$service}] Error in {$operation}: " . $exception->getMessage(), $context);
    }

    /**
     * Log memory usage
     */
    public function logMemory(string $service, string $operation, array $context = []): void
    {
        if (!$this->logSwitches['memory_tracking']) {
            return;
        }

        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryUsageMB = round($memoryUsage / 1024 / 1024, 2);
        $memoryPeakMB = round($memoryPeak / 1024 / 1024, 2);

        $context = $this->enrichContext($context, [
            'service' => $service,
            'operation' => $operation,
            'memory_usage_mb' => $memoryUsageMB,
            'memory_peak_mb' => $memoryPeakMB,
            'memory_warning' => $memoryUsageMB > $this->performanceThresholds['memory_warning_mb'],
        ]);

        $level = $memoryUsageMB > $this->performanceThresholds['memory_warning_mb'] ? 'warning' : 'debug';
        Log::log($level, "[{$service}] Memory: {$operation}", $context);
    }

    /**
     * Enrich context with common information
     */
    protected function enrichContext(array $context, array $additional = []): array
    {
        $enriched = array_merge($context, $additional);

        if ($this->logSwitches['request_context']) {
            $enriched = array_merge($enriched, [
                'request_id' => request()->header('X-Request-ID', uniqid()),
                'user_id' => Auth::id(),
                'timestamp' => now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $enriched;
    }

    /**
     * Check if a log switch is enabled
     */
    public function isEnabled(string $switch): bool
    {
        return $this->logSwitches[$switch] ?? false;
    }

    /**
     * Enable/disable log switches dynamically
     */
    public function setSwitch(string $switch, bool $enabled): void
    {
        $this->logSwitches[$switch] = $enabled;
    }

    /**
     * Get all log switches
     */
    public function getSwitches(): array
    {
        return $this->logSwitches;
    }

    /**
     * Start performance tracking
     */
    public function startPerformanceTracking(): array
    {
        return [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
        ];
    }

    /**
     * End performance tracking and log results
     */
    public function endPerformanceTracking(string $service, string $operation, array $tracking, array $context = []): void
    {
        $duration = microtime(true) - $tracking['start_time'];
        $durationMs = $duration * 1000;

        $this->logPerformance($service, $operation, $durationMs, $context);

        if ($this->logSwitches['memory_tracking']) {
            $this->logMemory($service, $operation, $context);
        }
    }
}
