<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Services\Pipelines\PipelineLogService;
use App\Services\Pipelines\PipelineManagementService;
use App\Services\Pipelines\PipelineQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Pipeline resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PipelineLogService — records audit log entries for pipeline changes
 *   - PipelineManagementService — handles create, update, delete, and restore
 *      operations
 *   - PipelineQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PipelineController extends Controller
{
    /**
     * Service responsible for writing audit log entries for pipeline events.
     *
     * @var PipelineLogService
     */
    protected PipelineLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * pipelines.
     *
     * @var PipelineManagementService
     */
    protected PipelineManagementService $management;

    /**
     * Service responsible for querying and listing pipelines.
     *
     * @var PipelineQueryService
     */
    protected PipelineQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PipelineLogService $logger Handles audit logging for
     * pipeline events.
     *
     * @param  PipelineManagementService $management Handles pipeline
     * create/update/delete/restore.
     *
     * @param  PipelineQueryService $query Handles pipeline listing and
     * retrieval.
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
     * Also includes the authenticated user's permissions for the Pipeline
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated pipeline data with pagination metadata and
     * permissions.
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
     * Validation is handled upstream by StorePipelineRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePipelineRequest $request Validated request containing
     * pipeline data.
     *
     * @return JsonResponse The newly created pipeline, with HTTP 201 Created.
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
     * Returns a single pipeline by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Pipeline $pipeline Route-model-bound pipeline instance.
     *
     * @return JsonResponse The resolved pipeline resource.
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
     * Validation is handled upstream by UpdatePipelineRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePipelineRequest $request Validated request containing
     * updated pipeline data.
     *
     * @param  Pipeline $pipeline Route-model-bound pipeline instance to update.
     *
     * @return JsonResponse The updated pipeline resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * pipeline instance is still fully accessible during logging.
     *
     * @param  Pipeline $pipeline Route-model-bound pipeline instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified pipeline from soft deletion.
     *
     * Looks up the pipeline including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the pipeline is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted pipeline.
     *
     * @return JsonResponse The restored pipeline resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the pipeline is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $pipeline = Pipeline::withTrashed()->findOrFail($id);
        $this->authorize('restore', $pipeline);

        if (! $pipeline->trashed()) {
            abort(404);
        }

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
