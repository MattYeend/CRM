<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningRequest;
use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use App\Services\LearningLogService;
use Illuminate\Http\JsonResponse;
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
     *
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Learning::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Learning::with('users')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Learning $learning
     *
     * @return JsonResponse
     */
    public function show(Learning $learning): JsonResponse
    {
        $this->authorize('view', $learning);

        return response()->json($learning->load('users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLearningRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreLearningRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $learning = Learning::create($data);

        $this->logger->learningCreated(
            $user,
            $user->id,
            $learning
        );

        return response()->json($learning, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLearningRequest $request
     *
     * @param Learning $learning
     *
     * @return JsonResponse
     */
    public function update(
        UpdateLearningRequest $request,
        Learning $learning
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $learning->update($data);

        $this->logger->learningUpdated(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Learning $learning
     *
     * @return JsonResponse
     */
    public function destroy(Learning $learning): JsonResponse
    {
        $this->authorize('delete', $learning);

        $user = auth()->user();

        $this->logger->learningDeleted(
            $user,
            $user->id,
            $learning,
        );

        $learning->update([
            'deleted_by' => $user->id,
        ]);

        $learning->delete();

        return response()->json(null, 204);
    }

    /**
     * Mark the specified resource as completed.
     *
     * @param Request $request
     *
     * @param Learning $learning
     *
     * @return JsonResponse
     */
    public function complete(Request $request, Learning $learning): JsonResponse
    {
        $this->authorize('complete', $learning);

        $user = $request->user();

        $learning->update([
            'is_completed' => true,
            'completed_by' => $user->id,
            'completed_at' => now(),
        ]);

        $this->logger->learningComplete(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning);
    }

    /**
     * Mark the specified resource as incomplete.
     *
     * @param Request $request
     *
     * @param Learning $learning
     *
     * @return JsonResponse
     */
    public function incomplete(
        Request $request,
        Learning $learning
    ): JsonResponse {
        $this->authorize('incomplete', $learning);

        $user = $request->user();

        $learning->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        $this->logger->learningIncomplete(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning);
    }
}
