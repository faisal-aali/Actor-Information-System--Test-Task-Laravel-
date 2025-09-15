<?php

namespace App\Http\Controllers;

use App\Models\Actor;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Actor Information API",
 *     version="1.0.0",
 *     description="A comprehensive API for managing actor information submissions with AI-powered data extraction",
 *     @OA\Contact(
 *         email="faz.ali.bhamani@gmail.com",
 *         name="Faisal Aali"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Development Server"
 * )
 * 
 * @OA\Tag(
 *     name="Actors",
 *     description="Actor information management endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Validation",
 *     description="API validation and health check endpoints"
 * )
 */
class ActorController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * @OA\Get(
     *     path="/actors/form",
     *     summary="Display actor submission form",
     *     description="Returns the HTML form for submitting actor information",
     *     tags={"Actors"},
     *     @OA\Response(
     *         response=200,
     *         description="Form displayed successfully",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     */
    public function showForm()
    {
        return view('actor.form');
    }

    /**
     * @OA\Post(
     *     path="/actors",
     *     summary="Submit actor information",
     *     description="Processes actor information submission with AI-powered data extraction",
     *     tags={"Actors"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"email", "description"},
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Unique email address",
     *                     example="john.doe@example.com"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Detailed actor description including name and address",
     *                     example="John Doe is a 30-year-old male actor from 123 Main Street, New York. He is 6 feet tall and weighs 180 pounds."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to submissions page on success",
     *         @OA\Header(
     *             header="Location",
     *             description="Redirect URL",
     *             @OA\Schema(type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error during AI processing"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:actors,email|max:255',
            'description' => 'required|string|min:10|max:2000',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address has already been used.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'description.required' => 'Actor description is required.',
            'description.min' => 'Description must be at least 10 characters long.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Extract actor information using OpenAI
            $actorData = $this->openAIService->extractActorInfo($request->description);

            // Check if required fields are present
            if (empty($actorData['first_name']) || empty($actorData['last_name']) || empty($actorData['address'])) {
                return redirect()->back()
                    ->withErrors(['description' => 'Please add first name, last name, and address to your description.'])
                    ->withInput();
            }

            // Create actor record with XSS protection
            $actor = Actor::create([
                'email' => $request->email,
                'description' => strip_tags($request->description),
                'first_name' => strip_tags($actorData['first_name'] ?? null),
                'last_name' => strip_tags($actorData['last_name'] ?? null),
                'address' => strip_tags($actorData['address'] ?? null),
                'height' => strip_tags($actorData['height'] ?? null),
                'weight' => strip_tags($actorData['weight'] ?? null),
                'gender' => strip_tags($actorData['gender'] ?? null),
                'age' => $actorData['age'] ?? null,
            ]);

            return redirect()->route('actors.submissions')->with('success', 'Actor information submitted successfully!');

        } catch (\Exception $e) {
            \Log::error('Actor submission failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['description' => 'Failed to process actor information. Please try again.'])
                ->withInput();
        }
    }

    /**
     * @OA\Get(
     *     path="/actors/submissions",
     *     summary="Display actor submissions",
     *     description="Returns a table view of all submitted actor information",
     *     tags={"Actors"},
     *     @OA\Response(
     *         response=200,
     *         description="Submissions displayed successfully",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             @OA\Schema(type="string")
     *         )
     *     )
     * )
     */
    public function submissions()
    {
        $actors = Actor::orderBy('created_at', 'desc')->get();
        return view('actor.submissions', compact('actors'));
    }

    /**
     * @OA\Get(
     *     path="/api/actors/prompt-validation",
     *     summary="API prompt validation endpoint",
     *     description="Returns a simple JSON response for API validation",
     *     tags={"Validation"},
     *     @OA\Response(
     *         response=200,
     *         description="Validation successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="text_prompt"
     *             )
     *         )
     *     )
     * )
     */
    public function promptValidation()
    {
        return response()->json([
            'message' => 'text_prompt'
        ]);
    }
}
