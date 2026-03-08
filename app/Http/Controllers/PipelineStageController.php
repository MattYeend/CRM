<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineStageRequest;
use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;
use App\Services\PipelineStageLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    /**
     * Declare a protected property to hold the PipelineStageLogService instance
     *
     * @var PipelineStageLogService
     */
    protected PipelineStageLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param PipelineStageLogService $logger
     *
     * An instance of the PipelineStageLogService used for logging
     * pipeline stage-related actions
     */
    public function __construct(PipelineStageLogService $logger)
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
        $this->authorize('viewAny', PipelineStage::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            PipelineStage::with('pipeline')->paginate($perPage)
        );
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
        return response()->json($pipelineStage->load('pipeline'));
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $stage = PipelineStage::create($data);

        $this->logger->pipelineStageCreated(
            $user,
            $user->id,
            $stage
        );

        return response()->json($stage, 201);
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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $pipelineStage->update($data);

        $this->logger->pipelineStageUpdated(
            $user,
            $user->id,
            $pipelineStage
        );

        return response()->json($pipelineStage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PipelineStage $pipelineStage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PipelineStage $pipelineStage): JsonResponse
    {
        $this->authorize('delete', $pipelineStage);

        $user = auth()->user();

        $this->logger->pipelineStageDeleted(
            $user,
            $user->id,
            $pipelineStage
        );

        $pipelineStage->update([
            'deleted_by' => $user->id,
        ]);

        $pipelineStage->delete();

        return response()->json(null, 204);
    }
}
