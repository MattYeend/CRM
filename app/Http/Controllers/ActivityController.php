<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Declare a protected property to hold the ActivityLogService instance
     *
     * @var ActivityLogService
     */
    protected ActivityLogService $logger;
    /**
     * Constructor for the controller
     *
     * @param ActivityLogService $logger
     *
     * An instance of the ActivityLogService used for logging
     * activity-related actions
     */
    public function __construct(ActivityLogService $logger)
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
        $this->authorize('viewAny', Activity::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Activity::with('user', 'subject')->paginate($perPage)
        );
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

        return response()->json($activity->load('user', 'subject'));
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
        $user = $request->user();

        $data = $request->validated();

        $data['created_by'] = $user->id;

        $activity = Activity::create($data);

        $this->logger->activityCreated(
            $user,
            $user->id,
            $activity
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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $activity->update($data);

        $this->logger->activityUpdated(
            $request->user(),
            auth()->id(),
            $activity
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

        $this->logger->activityDeleted(
            auth()->user(),
            auth()->id(),
            $activity
        );

        $activity->deleted_by = auth()->id();
        $activity->save();

        $activity->delete();

        return response()->json(null, 204);
    }
}
