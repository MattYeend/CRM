<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLearningRequest;
use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use App\Services\Learnings\LearningLogService;
use App\Services\Learnings\LearningManagementService;
use App\Services\Learnings\LearningQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningController extends Controller
{
    /**
     * Declare a protected property to hold the LearningLogService,
     * LearningManagementService and LearningQueryService instance
     *
     * @var LearningLogService
     * @var LeadManagementService
     * @var LeadQueryService
     */
    protected LearningLogService $logger;
    protected LearningManagementService $management;
    protected LearningQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param LearningLogService $logger
     *
     * @param LearningManagementService $management
     *
     * @param LearningQueryService $query
     *
     * An instance of the LearningLogService used for logging
     * learning-related actions
     * An instance of the LearningManagementService for management
     * of learning
     * An instance of the LearningQueryService for the query of
     * learning-related actions
     */
    public function __construct(
        LearningLogService $logger,
        LearningManagementService $management,
        LearningQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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

        $learning = $this->query->list($request);

        return response()->json($learning);
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
        $learning = $this->management->store($request);

        $user = $request->user();

        $this->logger->learningCreated(
            $user,
            $user->id,
            $learning,
        );

        return response()->json($learning, 201);
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

        $learning = $this->query->show($learning);

        return response()->json($learning);
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
        $learning = $this->management->update($request, $learning);

        $user = $request->user();

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

        $this->management->destroy($learning);

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

        $this->logger->learningComplete(
            $user,
            $user->id,
            $learning,
        );

        $learning = $this->management->complete($learning);

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

        $this->logger->learningIncomplete(
            $user,
            $user->id,
            $learning,
        );

        $learning = $this->management->incomplete($learning);

        return response()->json($learning);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $learning = Learning::withTrashed()->findOrFail($id);
        $this->authorize('restore', $learning);

        if (! $learning->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->learningRestored(
            $user,
            $user->id,
            $learning
        );

        return response()->json($learning);
    }
}
