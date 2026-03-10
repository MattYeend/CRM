<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Services\PipelineLogService;
use App\Services\PipelineManagementService;
use App\Services\PipelineQueryService;
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
     * @var PipelineQueryServic
     */
    protected PipelineLogService $logger;
    protected PipelineManagementService $managementService;
    protected PipelineQueryService $queryService;

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
        PipelineManagementService $managementService,
        PipelineQueryService $queryService,
    ) {
        $this->logger = $logger;
        $this->managementService = $managementService;
        $this->queryService = $queryService;
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

        $pipeline = $this->queryService->list($request);

        return response()->json($pipeline);
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

        $pipeline = $this->queryService->show($pipeline);

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
        $pipeline = $this->managementService->store($request);

        $user = $request->user();

        $this->logger->pipelineCreated(
            $user,
            $user->id,
            $pipeline,
        );

        return response()->json($pipeline, 201);
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
        $pipeline = $this->managementService->update($request, $pipeline);

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

        $pipeline = $this->managementService->destroy($pipeline);

        return response()->json(null, 204);
    }
}
