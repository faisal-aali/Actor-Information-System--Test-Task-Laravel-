<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAIService
{
    /**
     * Extract actor information from description using OpenAI
     * 
     * @param string $description The actor description to process
     * @return array Extracted actor information
     * @throws \Exception When extraction fails
     */
    public function extractActorInfo(string $description): array
    {
        // Create a more human-like and detailed prompt
        $prompt = $this->buildHumanLikePrompt($description);

        try {
            // Add caching to avoid repeated API calls for similar descriptions
            $cacheKey = 'actor_extraction_' . md5($description);
            
            return Cache::remember($cacheKey, 3600, function () use ($prompt) {
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert data extraction assistant. Extract actor information from descriptions and return clean, structured JSON data.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 1000,
                ]);

                $content = $response->choices[0]->message->content;
                $data = $this->parseAndValidateResponse($content);

                return $data;
            });

        } catch (\Exception $e) {
            Log::error('OpenAI extraction failed', [
                'description' => substr($description, 0, 100) . '...',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('I apologize, but I encountered an issue while processing the actor information. Please try again with a more detailed description.');
        }
    }

    /**
     * Build a human-like prompt for better AI understanding
     */
    private function buildHumanLikePrompt(string $description): string
    {
        return "Please carefully analyze the following actor description and extract the requested information. Be thorough and accurate in your extraction.

ACTOR DESCRIPTION:
{$description}

Please extract the following information and return it as a clean JSON object:
- first_name: The actor's first name (required)
- last_name: The actor's last name (required) 
- address: The actor's address (required)
- height: Physical height (if mentioned)
- weight: Physical weight (if mentioned)
- gender: Gender/sex (if mentioned)
- age: Age in years (if mentioned)

IMPORTANT INSTRUCTIONS:
1. If any required field (first_name, last_name, address) is missing, set it to null
2. For optional fields, set to null if not found
3. Clean and standardize the data (e.g., convert height to a standard format)
4. Return ONLY valid JSON, no additional text
5. Be precise and avoid assumptions

Expected JSON format:
{
  \"first_name\": \"string or null\",
  \"last_name\": \"string or null\", 
  \"address\": \"string or null\",
  \"height\": \"string or null\",
  \"weight\": \"string or null\",
  \"gender\": \"string or null\",
  \"age\": \"integer or null\"
}";
    }

    /**
     * Parse and validate the AI response
     */
    private function parseAndValidateResponse(string $content): array
    {
        // Clean the response to extract JSON
        $content = trim($content);
        
        // Remove any markdown code blocks
        if (strpos($content, '```json') !== false) {
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/\s*```/', '', $content);
        } elseif (strpos($content, '```') !== false) {
            $content = preg_replace('/```\s*/', '', $content);
            $content = preg_replace('/\s*```/', '', $content);
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Invalid JSON from OpenAI', [
                'content' => $content,
                'json_error' => json_last_error_msg()
            ]);
            throw new \Exception('I received an invalid response from the AI service. Please try again.');
        }

        if (!$data || !is_array($data)) {
            throw new \Exception('The AI service returned empty data. Please provide a more detailed description.');
        }

        // Ensure all expected fields are present
        $expectedFields = ['first_name', 'last_name', 'address', 'height', 'weight', 'gender', 'age'];
        $result = [];
        
        foreach ($expectedFields as $field) {
            $result[$field] = $data[$field] ?? null;
        }

        return $result;
    }

    /**
     * Get AI service health status
     */
    public function getHealthStatus(): array
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => 'Hello, are you working?']
                ],
                'max_tokens' => 10,
            ]);

            return [
                'status' => 'healthy',
                'response_time' => microtime(true),
                'model' => 'gpt-3.5-turbo'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'model' => 'gpt-3.5-turbo'
            ];
        }
    }
}
