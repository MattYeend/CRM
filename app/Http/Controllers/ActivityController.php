<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Services\ActivityLogService;
use App\Services\ActivityManagementService;
use App\Services\ActivityQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Declare a protected property to hold the ActivityLogService,
     * ActivityManagementService and ActivityQueryService instance
     *
     * @var ActivityLogService
     * @var ActivityManagementService
     * @var ActivityQueryService
     */
    protected ActivityLogService $logger;
    protected ActivityManagementService $management;
    protected ActivityQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param ActivityLogService $logger
     *
     * @param ActivityManagementService $management
     *
     * @param ActivityQueryService $query
     *
     * An instance of the ActivityLogService used for logging
     * activity-related actions
     * An instance of the ActivityManagementService for management
     * of activities
     * An instance of the ActivityQueryService for the query of
     * activity-related actions
     */
    public function __construct(
        ActivityLogService $logger,
        ActivityManagementService $management,
        ActivityQueryService $query,
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
        $this->authorize('viewAny', Activity::class);

        $activities = $this->query->list($request);

        return response()->json($activities);
    }

    /**
     * Display the specified resource.
     *
     * @param Activity $activity
     *
     * @return JsonResponse
     */
    public function show(Activity $activity): JsonResponse
    {
        $this->authorize('view', $activity);

        $activity = $this->query->show($activity);

        return response()->json($activity);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreActivityRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $activity = $this->management->store($request);
        $user = $request->user();

        $this->logger->activityCreated(
            $user,
            $user->id,
            $activity,
        );

        return response()->json($activity, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateActivityRequest $request
     *
     * @param Activity $activity
     *
     * @return JsonResponse
     */
    public function update(
        UpdateActivityRequest $request,
        Activity $activity
    ): JsonResponse {
        $activity = $this->management->update($request, $activity);

        $user = $request->user();

        $this->logger->activityUpdated(
            $user,
            $user->id,
            $activity,
        );

        return response()->json($activity);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Activity $activity
     *
     * @return JsonResponse
     */
    public function destroy(Activity $activity): JsonResponse
    {
        $this->authorize('delete', $activity);

        $user = auth()->user();

        $this->logger->activityDeleted(
            $user,
            $user->id,
            $activity,
        );

        $this->management->destroy($activity);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified user from soft deletion.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $user = $this->management->restore((int) $id);

        $this->authorize('restore', $user);
        $auth = auth()->user();

        $this->logger->activityRestored(
            $auth,
            $auth->id,
            $user,
        );

        return response()->json($user);
    }
}
