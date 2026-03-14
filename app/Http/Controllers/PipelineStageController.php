<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineStageRequest;
use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;
use App\Services\PipelineStages\PipelineStageLogService;
use App\Services\PipelineStages\PipelineStageManagementService;
use App\Services\PipelineStages\PipelineStageQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    /**
     * Declare a protected property to hold the PipelineStageLogService,
     * PipelineStageManagementService and PipelineStageQueryService instance
     *
     * @var PipelineStageLogService
     * @var PipelineStageManagementService
     * @var PipelineStageQueryService
     */
    protected PipelineStageLogService $logger;
    protected PipelineStageManagementService $management;
    protected PipelineStageQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PipelineStageLogService $logger
     *
     * @param PipelineStageManagementService $management
     *
     * @param PipelineStageQueryService $query
     *
     * An instance of the PipelineStageLogService used for logging
     * pipeline stage-related actions
     * An instance of the PipelineStageManagementService for management
     * of pipeline stages
     * An instance of the PipelineStageQueryService for the query of
     * pipeline stage-related actions
     */
    public function __construct(
        PipelineStageLogService $logger,
        PipelineStageManagementService $management,
        PipelineStageQueryService $query,
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
        $this->authorize('viewAny', PipelineStage::class);

        $pipelineStage = $this->query->list($request);

        return response()->json($pipelineStage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePipelineStageRequest $request
     *
     * @return JsonResponse
     */
    public function store(StorePipelineStageRequest $request): JsonResponse
    {
        $pipelineStage = $this->management->store($request);

        $user = $request->user();
        $this->logger->pipelineStageCreated(
            $user,
            $user->id,
            $pipelineStage,
        );

        return response()->json($pipelineStage, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param PipelineStage $pipelineStage
     *
     * @return JsonResponse
     */
    public function show(PipelineStage $pipelineStage): JsonResponse
    {
        $this->authorize('view', $pipelineStage);

        $pipelineStage = $this->query->show($pipelineStage);

        return response()->json($pipelineStage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePipelineStageRequest $request
     *
     * @param PipelineStage $pipelineStage
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePipelineStageRequest $request,
        PipelineStage $pipelineStage
    ): JsonResponse {
        $pipelineStage =
            $this->management->update($request, $pipelineStage);

        $user = $request->user();

        $this->logger->pipelineStageUpdated(
            $user,
            $user->id,
            $pipelineStage,
        );

        return response()->json($pipelineStage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PipelineStage $pipelineStage
     *
     * @return JsonResponse
     */
    public function destroy(PipelineStage $pipelineStage): JsonResponse
    {
        $this->authorize('delete', $pipelineStage);

        $user = auth()->user();

        $this->logger->pipelineStageDeleted(
            $user,
            $user->id,
            $pipelineStage,
        );

        $this->management->destroy($pipelineStage);

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
        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);
        $this->authorize('restore', $pipelineStage);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->pipelineStageRestored(
            $user,
            $user->id,
            $pipelineStage
        );

        return response()->json($pipelineStage);
    }
}
