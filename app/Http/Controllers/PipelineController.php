<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Services\PipelineLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    /**
     * Declare a protected property to hold the PipelineLogService instance
     *
     * @var PipelineLogService
     */
    protected PipelineLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param PipelineLogService $logger
     *
     * An instance of the PipelineLogService used for logging
     * pipeline-related actions
     */
    public function __construct(PipelineLogService $logger)
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
        $this->authorize('viewAny', Pipeline::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Pipeline::with('stages')->paginate($perPage)
        );
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

        return response()->json($pipeline->load('stages'));
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
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $pipeline = Pipeline::create($data);

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
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $pipeline->update($data);

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

        $pipeline->update([
            'deleted_by' => $user->id,
        ]);

        $pipeline->delete();

        return response()->json(null, 204);
    }
}
