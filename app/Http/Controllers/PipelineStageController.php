<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineStageRequest;
use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;
use App\Services\PipelineStages\PipelineStageLogService;
use App\Services\PipelineStages\PipelineStageManagementService;
use App\Services\PipelineStages\PipelineStageQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the PipelineStage resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PipelineStageLogService — records audit log entries for pipeline stage
 *      changes
 *   - PipelineStageManagementService — handles create, update, delete, and
 *      restore operations
 *   - PipelineStageQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PipelineStageController extends Controller
{
    /**
     * Service responsible for writing audit log entries for pipeline
     * stage events.
     *
     * @var PipelineStageLogService
     */
    protected PipelineStageLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * pipeline stages.
     *
     * @var PipelineStageManagementService
     */
    protected PipelineStageManagementService $management;

    /**
     * Service responsible for querying and listing pipeline stages.
     *
     * @var PipelineStageQueryService
     */
    protected PipelineStageQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PipelineStageLogService $logger Handles audit logging for
     * pipeline stage events.
     *
     * @param  PipelineStageManagementService $management Handles pipeline
     * stage create/update/delete/restore.
     *
     * @param  PipelineStageQueryService $query Handles pipeline stage listing
     * and retrieval.
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
     * Also includes the authenticated user's permissions for the PipelineStage
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated pipeline stage data with pagination
     * metadata and permissions.
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
     * Validation is handled upstream by StorePipelineStageRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePipelineStageRequest $request Validated request containing
     * pipelinestage data.
     *
     * @return JsonResponse The newly created pipelinestage, with HTTP 201
     * Created.
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
     * Returns a single pipeline stage by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  PipelineStage $pipelineStage Route-model-bound
     * pipeline stage instance.
     *
     * @return JsonResponse The resolved pipeline stage resource.
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
     * Validation is handled upstream by UpdatePipelineStageRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePipelineStageRequest $request Validated request containing
     * updated pipeline stage data.
     *
     * @param  PipelineStage $pipelineStage Route-model-bound pipeline stage
     * instance to update.
     *
     * @return JsonResponse The updated pipeline stage resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * pipeline stage instance is still fully accessible during logging.
     *
     * @param  PipelineStage $pipelineStage Route-model-bound pipeline
     * stage instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified pipeline stage from soft deletion.
     *
     * Looks up the pipeline stage including trashed records, then authorises
     * via the 'restore' policy. Returns 404 if the pipeline stage is not
     * currently soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted pipeline
     * stage.
     *
     * @return JsonResponse The restored pipeline stage resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the pipeline stage is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);
        $this->authorize('restore', $pipelineStage);

        if (! $pipelineStage->trashed()) {
            abort(404);
        }

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
