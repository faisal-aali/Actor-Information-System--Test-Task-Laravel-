<?php

namespace App\Http\Controllers;

use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HealthController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    public function index()
    {
        $startTime = microtime(true);
        $healthStatus = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => '1.0.0',
            'services' => [],
            'metrics' => $this->getSystemMetrics(),
            'response_time_ms' => 0
        ];

        $errors = [];

        // Check database health
        $dbHealth = $this->checkDatabaseHealth();
        $healthStatus['services']['database'] = $dbHealth;
        if ($dbHealth['status'] !== 'healthy') {
            $errors[] = 'Database service is unhealthy';
        }

        // Check cache health
        $cacheHealth = $this->checkCacheHealth();
        $healthStatus['services']['cache'] = $cacheHealth;
        if ($cacheHealth['status'] !== 'healthy') {
            $errors[] = 'Cache service is unhealthy';
        }

        // Check OpenAI service health
        $openaiHealth = $this->checkOpenAIHealth();
        $healthStatus['services']['openai'] = $openaiHealth;
        if ($openaiHealth['status'] !== 'healthy') {
            $errors[] = 'OpenAI service is unhealthy';
        }

        // Determine overall status
        if (!empty($errors)) {
            $healthStatus['status'] = 'unhealthy';
            $healthStatus['errors'] = $errors;
        }

        $healthStatus['response_time_ms'] = round((microtime(true) - $startTime) * 1000, 2);

        $statusCode = $healthStatus['status'] === 'healthy' ? 200 : 503;

        return response()->json($healthStatus, $statusCode);
    }

    private function checkDatabaseHealth(): array
    {
        $startTime = microtime(true);
        
        try {
            DB::connection()->getPdo();
            $result = DB::select('SELECT 1 as test');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime,
                'database_size_mb' => $this->getDatabaseSize()
            ];
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkCacheHealth(): array
    {
        $startTime = microtime(true);
        
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            Cache::put($testKey, $testValue, 60);
            $retrievedValue = Cache::get($testKey);
            
            if ($retrievedValue !== $testValue) {
                throw new \Exception('Cache read/write test failed');
            }
            
            Cache::forget($testKey);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return [
                'status' => 'healthy',
                'response_time_ms' => $responseTime
            ];
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkOpenAIHealth(): array
    {
        $startTime = microtime(true);
        
        try {
            $healthStatus = $this->openAIService->getHealthStatus();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return array_merge($healthStatus, [
                'response_time_ms' => $responseTime
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI health check failed', ['error' => $e->getMessage()]);
            
            return [
                'status' => 'unhealthy',
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'error' => $e->getMessage()
            ];
        }
    }

    private function getSystemMetrics(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'uptime_seconds' => time() - $_SERVER['REQUEST_TIME_FLOAT']
        ];
    }

    private function getDatabaseSize(): float
    {
        try {
            $databasePath = database_path('database.sqlite');
            if (file_exists($databasePath)) {
                return round(filesize($databasePath) / 1024 / 1024, 2);
            }
            return 0.0;
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}