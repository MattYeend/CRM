<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobTitleRequest;
use App\Http\Requests\UpdateJobTitleRequest;
use App\Models\JobTitle;
use App\Services\JobTitles\JobTitleLogService;
use App\Services\JobTitles\JobTitleManagementService;
use App\Services\JobTitles\JobTitleQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobTitleController extends Controller
{
    /**
     * Declare a protected property to hold the JobTitleLogService,
     * JobTitleManagementService and JobTitleQueryService instance
     *
     * @var JobTitleLogService
     * @var JobTitleManagementService
     * @var JobTitleQueryServic
     */
    protected JobTitleLogService $logger;
    protected JobTitleManagementService $management;
    protected JobTitleQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param JobTitleLogService $logger
     *
     * @param JobTitleManagementService $management
     *
     * @param JobTitleQueryService $query
     *
     * An instance of the JobTitleLogService used for logging
     * job title-related actions
     * An instance of the JobTitleManagementService for management
     * of job titles
     * An instance of the JobTitleQueryService for the query of
     * job title-related actions
     */
    public function __construct(
        JobTitleLogService $logger,
        JobTitleManagementService $management,
        JobTitleQueryService $query,
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
        $this->authorize('viewAny', JobTitle::class);

        $jobTitle = $this->query->list($request);

        return response()->json($jobTitle);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreJobTitleRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreJobTitleRequest $request): JsonResponse
    {
        $jobTitle = $this->management->store($request);

        $user = $request->user();

        $this->logger->jobTitleCreated(
            $user,
            $user->id,
            $jobTitle,
        );

        return response()->json($jobTitle, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param JobTitle $jobTitle
     *
     * @return JsonResponse
     */
    public function show(JobTitle $jobTitle): JsonResponse
    {
        $this->authorize('view', $jobTitle);

        $jobTitle = $this->query->show($jobTitle);

        return response()->json($jobTitle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateJobTitleRequest $request
     *
     * @param JobTitle $jobTitle
     *
     * @return JsonResponse
     */
    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle
    ): JsonResponse {
        $jobTitle = $this->management->update($request, $jobTitle);

        $user = $request->user();

        $this->logger->jobTitleUpdated(
            $user,
            $user->id,
            $jobTitle,
        );

        return response()->json($jobTitle);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param JobTitle $jobTitle
     *
     * @return JsonResponse
     */
    public function destroy(JobTitle $jobTitle): JsonResponse
    {
        $this->authorize('delete', $jobTitle);

        $user = auth()->user();

        $this->logger->jobTitleDeleted(
            $user,
            $user->id,
            $jobTitle,
        );

        $this->management->destroy($jobTitle);

        return response()->json(null, 204);
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
        $jobTitle = JobTitle::withTrashed()->findOrFail($id);
        $this->authorize('restore', $jobTitle);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->jobTitleRestored(
            $user,
            $user->id,
            $jobTitle
        );

        return response()->json($jobTitle);
    }
}
