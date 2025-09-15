<?php

namespace Tests\Feature;

use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ActorTest extends TestCase
{
    use RefreshDatabase;

    public function test_actor_form_displays_correctly(): void
    {
        $response = $this->get('/actors/form');

        $response->assertStatus(200);
        $response->assertSee('Actor Information Submission');
        $response->assertSee('Email Address');
        $response->assertSee('Actor Description');
    }

    public function test_actor_form_validation_works(): void
    {
        $response = $this->post('/actors', []);

        $response->assertSessionHasErrors(['email', 'description']);
    }

    public function test_actor_form_requires_unique_email(): void
    {
        Actor::create([
            'email' => 'test@example.com',
            'description' => 'Test actor description',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St',
        ]);

        $response = $this->post('/actors', [
            'email' => 'test@example.com',
            'description' => 'Another test description',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_actor_submissions_page_displays_correctly(): void
    {
        $response = $this->get('/actors/submissions');

        $response->assertStatus(200);
        $response->assertSee('Actor Submissions');
    }

    public function test_api_prompt_validation_endpoint(): void
    {
        $response = $this->get('/api/actors/prompt-validation');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'text_prompt'
        ]);
    }
}
