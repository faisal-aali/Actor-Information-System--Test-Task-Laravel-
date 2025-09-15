<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ActorIntegrationTest extends TestCase
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

    public function test_complete_actor_submission_workflow()
    {
        $actorData = [
            'email' => 'john.doe@example.com',
            'description' => 'John Doe is a 30-year-old male actor from 123 Main Street, New York. He is 6 feet 2 inches tall and weighs 180 pounds.'
        ];

        $response = $this->post('/actors', $actorData);

        $response->assertRedirect('/actors/submissions');
        $response->assertSessionHas('success', 'Actor information submitted successfully!');

        $this->assertDatabaseHas('actors', [
            'email' => 'john.doe@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main Street, New York, NY 10001',
            'height' => '6 feet 2 inches',
            'weight' => '180 pounds',
            'gender' => 'male',
            'age' => 30
        ]);
    }

    public function test_actor_submission_with_missing_required_fields()
    {
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('extractActorInfo')
                ->andReturn([
                    'first_name' => 'John',
                    'last_name' => null, // Missing last name
                    'address' => '123 Main Street',
                    'height' => null,
                    'weight' => null,
                    'gender' => null,
                    'age' => null
                ]);
        });

        $actorData = [
            'email' => 'john@example.com',
            'description' => 'John is an actor from 123 Main Street'
        ];

        $response = $this->post('/actors', $actorData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['description']);
        $response->assertSessionHas('errors', function ($errors) {
            return $errors->first('description') === 'Please add first name, last name, and address to your description.';
        });

        $this->assertDatabaseMissing('actors', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_actor_submission_with_openai_failure()
    {
        $this->mock(OpenAIService::class, function ($mock) {
            $mock->shouldReceive('extractActorInfo')
                ->andThrow(new \Exception('OpenAI API Error'));
        });

        Log::shouldReceive('error')
            ->once()
            ->with('Actor submission failed', Mockery::type('array'));

        $actorData = [
            'email' => 'test@example.com',
            'description' => 'Test actor description'
        ];

        $response = $this->post('/actors', $actorData);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['description']);
        $response->assertSessionHas('errors', function ($errors) {
            return str_contains($errors->first('description'), 'Failed to process actor information');
        });
    }

    public function test_actor_submission_validation_rules()
    {
        // Test missing email
        $response = $this->post('/actors', [
            'description' => 'Test description'
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test invalid email format
        $response = $this->post('/actors', [
            'email' => 'invalid-email',
            'description' => 'Test description'
        ]);
        $response->assertSessionHasErrors(['email']);

        // Test missing description
        $response = $this->post('/actors', [
            'email' => 'test@example.com'
        ]);
        $response->assertSessionHasErrors(['description']);

        // Test description too short
        $response = $this->post('/actors', [
            'email' => 'test@example.com',
            'description' => 'Short'
        ]);
        $response->assertSessionHasErrors(['description']);

        // Test description too long
        $response = $this->post('/actors', [
            'email' => 'test@example.com',
            'description' => str_repeat('a', 2001)
        ]);
        $response->assertSessionHasErrors(['description']);
    }

    public function test_actor_submission_with_duplicate_email()
    {
        // Create existing actor
        Actor::create([
            'email' => 'existing@example.com',
            'description' => 'Existing actor',
            'first_name' => 'Existing',
            'last_name' => 'Actor',
            'address' => '123 Test St'
        ]);

        $response = $this->post('/actors', [
            'email' => 'existing@example.com',
            'description' => 'New actor description'
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('actors', 1);
    }

    public function test_actor_submissions_page_displays_data_correctly()
    {
        // Create test actors
        $actors = collect([
            [
                'email' => 'actor1@example.com',
                'description' => 'First actor',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address' => '123 Main St',
                'height' => '6 feet',
                'weight' => '180 lbs',
                'gender' => 'male',
                'age' => 30
            ],
            [
                'email' => 'actor2@example.com',
                'description' => 'Second actor',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'address' => '456 Oak Ave',
                'height' => null,
                'weight' => null,
                'gender' => 'female',
                'age' => 25
            ]
        ])->map(function ($data) {
            return Actor::create($data);
        });

        $response = $this->get('/actors/submissions');

        $response->assertStatus(200);
        $response->assertSee('Actor Submissions');
        $response->assertSee('John');
        $response->assertSee('Jane');
        $response->assertSee('123 Main St');
        $response->assertSee('456 Oak Ave');
        $response->assertSee('male');
        $response->assertSee('6 feet');
        $response->assertSee('N/A'); // For missing data
    }

    public function test_actor_submissions_page_with_no_data()
    {
        $response = $this->get('/actors/submissions');

        $response->assertStatus(200);
        $response->assertSee('No actor submissions yet');
        $response->assertSee('Be the first to submit actor information!');
    }

    public function test_api_prompt_validation_endpoint()
    {
        $response = $this->get('/api/actors/prompt-validation');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'text_prompt'
        ]);
    }

    public function test_form_displays_correctly()
    {
        $response = $this->get('/actors/form');

        $response->assertStatus(200);
        $response->assertSee('Actor Information Submission');
        $response->assertSee('Email Address');
        $response->assertSee('Actor Description');
        $response->assertSee('Please enter your first name and last name, and also provide your address.');
        $response->assertSee('Submit Actor Information');
    }

    public function test_form_validation_error_display()
    {
        $response = $this->post('/actors', []);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['email', 'description']);

        // Follow redirect to see error display
        $response = $this->get('/actors/form');
        $response->assertSee('The email field is required');
        $response->assertSee('The description field is required');
    }

    public function test_form_preserves_input_on_validation_error()
    {
        $response = $this->post('/actors', [
            'email' => 'test@example.com',
            'description' => 'Short' // Too short
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['description']);

        // Follow redirect to see preserved input
        $response = $this->get('/actors/form');
        $response->assertSee('value="test@example.com"');
        $response->assertSee('Short');
    }

    public function test_caching_behavior()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with(Mockery::type('string'), 3600, Mockery::type('Closure'))
            ->andReturn([
                'first_name' => 'Cached',
                'last_name' => 'Actor',
                'address' => '123 Cache St',
                'height' => null,
                'weight' => null,
                'gender' => null,
                'age' => null
            ]);

        $response = $this->post('/actors', [
            'email' => 'cached@example.com',
            'description' => 'This should be cached'
        ]);

        $response->assertRedirect('/actors/submissions');
        $this->assertDatabaseHas('actors', [
            'email' => 'cached@example.com',
            'first_name' => 'Cached',
            'last_name' => 'Actor',
            'address' => '123 Cache St'
        ]);
    }

    public function test_actor_model_relationships_and_casts()
    {
        $actor = Actor::create([
            'email' => 'test@example.com',
            'description' => 'Test actor',
            'first_name' => 'Test',
            'last_name' => 'Actor',
            'address' => '123 Test St',
            'age' => 30
        ]);

        $this->assertInstanceOf(Actor::class, $actor);
        $this->assertEquals(30, $actor->age); // Should be cast to integer
        $this->assertIsInt($actor->age);
    }

    public function test_database_constraints()
    {
        // Test unique email constraint
        Actor::create([
            'email' => 'unique@example.com',
            'description' => 'First actor',
            'first_name' => 'First',
            'last_name' => 'Actor',
            'address' => '123 First St'
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Actor::create([
            'email' => 'unique@example.com', // Duplicate email
            'description' => 'Second actor',
            'first_name' => 'Second',
            'last_name' => 'Actor',
            'address' => '456 Second St'
        ]);
    }
}