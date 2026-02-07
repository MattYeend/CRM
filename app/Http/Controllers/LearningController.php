<?php

namespace App\Http\Controllers;

use App\Models\Learning;
use App\Services\LearningLogService;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    /**
     * Declare a protected property to hold the LearningLogService instance
     *
     * @var LearningLogService
     */
    protected LearningLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param LearningLogService $logger
     * An instance of the LearningLogService used for logging
     * note-related actions
     */
    public function __construct(LearningLogService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Learning::with('users')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Learning $learning
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Learning $learning)
    {
        return response()->json($learning->load('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $learning = Learning::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        $this->logger->learningCreated(
            $request->user(),
            $request->user()->id,
            $learning,
        );

        return response()->json($learning, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Learning $learning
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Learning $learning)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'boolean',
        ]);

        $learning->update($validated);

        $this->logger->learningUpdated(
            $request->user(),
            $request->user()->id,
            $learning,
        );

        return response()->json($learning);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Learning $learning
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Learning $learning)
    {
        $learning->delete();

        $this->logger->learningDeleted(
            auth()->user(),
            auth()->user()->id,
            $learning,
        );

        return response()->json(null, 204);
    }

    /**
     * Mark the specified resource as completed.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Learning $learning
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(Request $request, Learning $learning)
    {
        $learning->update([
            'is_completed' => true,
            'completed_by' => $request->user()->id,
            'completed_at' => now(),
        ]);

        $this->logger->learningComplete(
            $request->user(),
            $request->user()->id,
            $learning,
        );

        return response()->json($learning);
    }

    /**
     * Mark the specified resource as incomplete.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Learning $learning
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function incomplete(Request $request, Learning $learning)
    {
        $learning->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        $this->logger->learningIncomplete(
            $request->user(),
            $request->user()->id,
            $learning,
        );

        return response()->json($learning);
    }
}
