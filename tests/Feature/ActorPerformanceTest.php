<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ActorPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockOpenAIService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockOpenAIService()
    {
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('extractActorInfo')
                ->andReturn([
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'address' => '123 Main Street, New York, NY 10001',
                    'height' => '6 feet 2 inches',
                    'weight' => '180 pounds',
                    'gender' => 'male',
                    'age' => 30
                ]);
        });
    }

    public function test_form_page_loads_quickly()
    {
        $startTime = microtime(true);
        
        $response = $this->get('/actors/form');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $loadTime, 'Form page should load in less than 1 second');
    }

    public function test_submissions_page_loads_quickly_with_large_dataset()
    {
        // Create 1000 actors for performance testing
        $this->createLargeDataset(1000);

        $startTime = microtime(true);
        
        $response = $this->get('/actors/submissions');
        
        $endTime = microtime(true);
        $loadTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $loadTime, 'Submissions page should load in less than 2 seconds with 1000 records');
    }

    public function test_actor_submission_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->post('/actors', [
            'email' => 'performance@test.com',
            'description' => 'Performance test actor description with sufficient length'
        ]);
        
        $endTime = microtime(true);
        $processingTime = $endTime - $startTime;

        $response->assertRedirect('/actors/submissions');
        $this->assertLessThan(3.0, $processingTime, 'Actor submission should process in less than 3 seconds');
    }

    public function test_database_query_performance()
    {
        // Create test data
        $this->createLargeDataset(100);

        $startTime = microtime(true);
        
        $actors = Actor::orderBy('created_at', 'desc')->get();
        
        $endTime = microtime(true);
        $queryTime = $endTime - $startTime;

        $this->assertCount(100, $actors);
        $this->assertLessThan(0.5, $queryTime, 'Database query should execute in less than 0.5 seconds');
    }

    public function test_memory_usage_with_large_dataset()
    {
        $initialMemory = memory_get_usage();
        
        // Create large dataset
        $this->createLargeDataset(500);
        
        $memoryAfterCreation = memory_get_usage();
        $memoryUsed = $memoryAfterCreation - $initialMemory;
        
        // Memory usage should be reasonable (less than 50MB)
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB for 500 records');
    }

    public function test_concurrent_actor_submissions()
    {
        $startTime = microtime(true);
        
        $responses = [];
        $concurrentRequests = 10;
        
        // Simulate concurrent submissions
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $responses[] = $this->post('/actors', [
                'email' => "concurrent{$i}@test.com",
                'description' => "Concurrent test actor {$i} description with sufficient length"
            ]);
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        // All requests should succeed
        foreach ($responses as $response) {
            $response->assertRedirect('/actors/submissions');
        }

        // Should handle concurrent requests efficiently
        $this->assertLessThan(10.0, $totalTime, 'Concurrent requests should complete in less than 10 seconds');
    }

    public function test_caching_performance_improvement()
    {
        $description = 'Cached test actor description with sufficient length for performance testing';
        
        // First request (should cache)
        $startTime = microtime(true);
        $response1 = $this->post('/actors', [
            'email' => 'cache1@test.com',
            'description' => $description
        ]);
        $firstRequestTime = microtime(true) - $startTime;

        // Second request with similar description (should use cache)
        $startTime = microtime(true);
        $response2 = $this->post('/actors', [
            'email' => 'cache2@test.com',
            'description' => $description . ' slightly different'
        ]);
        $secondRequestTime = microtime(true) - $startTime;

        $response1->assertRedirect('/actors/submissions');
        $response2->assertRedirect('/actors/submissions');

        // Second request should be faster due to caching
        $this->assertLessThan($firstRequestTime, $secondRequestTime, 'Cached request should be faster');
    }

    public function test_database_index_performance()
    {
        // Create large dataset
        $this->createLargeDataset(1000);

        // Test email lookup performance (should use index)
        $startTime = microtime(true);
        
        $actor = Actor::where('email', 'actor500@test.com')->first();
        
        $endTime = microtime(true);
        $lookupTime = $endTime - $startTime;

        $this->assertNotNull($actor);
        $this->assertLessThan(0.1, $lookupTime, 'Email lookup should be very fast with proper indexing');
    }

    public function test_pagination_performance()
    {
        // Create large dataset
        $this->createLargeDataset(2000);

        $startTime = microtime(true);
        
        // Test pagination
        $actors = Actor::orderBy('created_at', 'desc')->paginate(50);
        
        $endTime = microtime(true);
        $paginationTime = $endTime - $startTime;

        $this->assertCount(50, $actors->items());
        $this->assertLessThan(1.0, $paginationTime, 'Pagination should be fast even with large datasets');
    }

    public function test_api_endpoint_performance()
    {
        $startTime = microtime(true);
        
        $response = $this->getJson('/api/actors/prompt-validation');
        
        $endTime = microtime(true);
        $apiTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.1, $apiTime, 'API endpoint should respond very quickly');
    }

    public function test_memory_cleanup_after_large_operations()
    {
        $initialMemory = memory_get_usage();
        
        // Perform large operation
        $this->createLargeDataset(1000);
        
        // Force garbage collection
        gc_collect_cycles();
        
        $finalMemory = memory_get_usage();
        $memoryIncrease = $finalMemory - $initialMemory;
        
        // Memory increase should be reasonable
        $this->assertLessThan(100 * 1024 * 1024, $memoryIncrease, 'Memory increase should be less than 100MB');
    }

    public function test_database_connection_pooling()
    {
        $startTime = microtime(true);
        
        // Test multiple database operations
        for ($i = 0; $i < 100; $i++) {
            Actor::create([
                'email' => "pooling{$i}@test.com",
                'description' => "Database pooling test {$i}",
                'first_name' => "Test{$i}",
                'last_name' => "Actor{$i}",
                'address' => "{$i} Test Street"
            ]);
        }
        
        $endTime = microtime(true);
        $totalTime = $endTime - $startTime;

        $this->assertLessThan(5.0, $totalTime, 'Database operations should be efficient');
        $this->assertDatabaseCount('actors', 100);
    }

    public function test_error_handling_performance()
    {
        $startTime = microtime(true);
        
        // Test error handling doesn't impact performance
        $response = $this->post('/actors', [
            'email' => 'invalid-email',
            'description' => 'Short'
        ]);
        
        $endTime = microtime(true);
        $errorTime = $endTime - $startTime;

        $response->assertSessionHasErrors();
        $this->assertLessThan(1.0, $errorTime, 'Error handling should be fast');
    }

    private function createLargeDataset($count)
    {
        $actors = [];
        
        for ($i = 0; $i < $count; $i++) {
            $actors[] = [
                'email' => "actor{$i}@test.com",
                'description' => "Test actor {$i} description with sufficient length for performance testing",
                'first_name' => "Actor{$i}",
                'last_name' => "Test{$i}",
                'address' => "{$i} Test Street, Test City, TC 12345",
                'height' => "6 feet {$i} inches",
                'weight' => (150 + $i) . " pounds",
                'gender' => $i % 2 === 0 ? 'male' : 'female',
                'age' => 20 + ($i % 50),
                'created_at' => now()->subDays($i),
                'updated_at' => now()->subDays($i)
            ];
        }

        // Use chunked insertion for better performance
        collect($actors)->chunk(100)->each(function ($chunk) {
            Actor::insert($chunk->toArray());
        });
    }
}