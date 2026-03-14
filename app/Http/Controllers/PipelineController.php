<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Services\Pipelines\PipelineLogService;
use App\Services\Pipelines\PipelineManagementService;
use App\Services\Pipelines\PipelineQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    /**
     * Declare a protected property to hold the PipelineLogService,
     * PipelineManagementService and PipelineQueryService instance
     *
     * @var PipelineLogService
     * @var PipelineManagementService
     * @var PipelineQueryService
     */
    protected PipelineLogService $logger;
    protected PipelineManagementService $management;
    protected PipelineQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PipelineLogService $logger
     *
     * @param PipelineManagementService $management
     *
     * @param PipelineQueryService $query
     *
     * An instance of the PipelineLogService used for logging
     * pipeline-related actions
     * An instance of the PipelineManagementService for management
     * of pipelines
     * An instance of the PipelineQueryService for the query of
     * pipeline-related actions
     */
    public function __construct(
        PipelineLogService $logger,
        PipelineManagementService $management,
        PipelineQueryService $query,
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
        $this->authorize('viewAny', Pipeline::class);

        $pipeline = $this->query->list($request);

        return response()->json($pipeline);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePipelineRequest $request
     *
     * @return JsonResponse
     */
    public function store(StorePipelineRequest $request): JsonResponse
    {
        $pipeline = $this->management->store($request);

        $user = $request->user();

        $this->logger->pipelineCreated(
            $user,
            $user->id,
            $pipeline,
        );

        return response()->json($pipeline, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Pipeline $pipeline
     *
     * @return JsonResponse
     */
    public function show(Pipeline $pipeline): JsonResponse
    {
        $this->authorize('view', $pipeline);

        $pipeline = $this->query->show($pipeline);

        return response()->json($pipeline);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePipelineRequest $request
     *
     * @param Pipeline $pipeline
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): JsonResponse {
        $pipeline = $this->management->update($request, $pipeline);

        $user = $request->user();

        $this->logger->pipelineUpdated(
            $user,
            $user->id,
            $pipeline,
        );

        return response()->json($pipeline);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Pipeline $pipeline
     *
     * @return JsonResponse
     */
    public function destroy(Pipeline $pipeline): JsonResponse
    {
        $this->authorize('delete', $pipeline);

        $user = auth()->user();

        $this->logger->pipelineDeleted(
            $user,
            $user->id,
            $pipeline,
        );

        $this->management->destroy($pipeline);

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
        $pipeline = Pipeline::withTrashed()->findOrFail($id);
        $this->authorize('restore', $pipeline);
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->pipelineRestored(
            $user,
            $user->id,
            $pipeline
        );

        return response()->json($pipeline);
    }
}
