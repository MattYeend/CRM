<?php

namespace App\Http\Controllers;

use App\Models\PipelineStage;
use App\Services\PipelineStageLogService;
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        return response()->json(
            PipelineStage::with('pipeline')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param PipelineStage $pipelineStage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(PipelineStage $pipelineStage)
    {
        return response()->json($pipelineStage->load('pipeline'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'pipeline_id' => 'required|integer|exists:pipelines,id',
            'name' => 'required|string',
            'position' => 'nullable|integer',
            'is_won_stage' => 'nullable|boolean',
            'is_lost_stage' => 'nullable|boolean',
        ]);

        $stage = PipelineStage::create($data);

        $this->logger->pipelineStageCreated(
            $request->user(),
            $request->user()->id,
            $stage
        );

        return response()->json($stage, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param PipelineStage $pipelineStage
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, PipelineStage $pipelineStage)
    {
        $data = $request->validate([
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'name' => 'sometimes|required|string',
            'position' => 'nullable|integer',
            'is_won_stage' => 'nullable|boolean',
            'is_lost_stage' => 'nullable|boolean',
        ]);

        $pipelineStage->update($data);

        $this->logger->pipelineStageUpdated(
            $request->user(),
            $request->user()->id,
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
    public function destroy(PipelineStage $pipelineStage)
    {
        $this->logger->pipelineStageDeleted(
            request()->user(),
            request()->user()->id,
            $pipelineStage
        );

        $pipelineStage->delete();

        return response()->json(null, 204);
    }
}
