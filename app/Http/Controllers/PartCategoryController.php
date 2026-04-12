<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;
use App\Services\PartCategories\PartCategoryLogService;
use App\Services\PartCategories\PartCategoryManagementService;
use App\Services\PartCategories\PartCategoryQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the PartCategory resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PartCategoryLogService — records audit log entries for part category
 *      changes
 *   - PartCategoryManagementService — handles create, update, delete, and
 *      restore operations
 *   - PartCategoryQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PartCategoryController extends Controller
{
    /**
     * Service responsible for writing audit log entries for part category
     * events.
     *
     * @var PartCategoryLogService
     */
    protected PartCategoryLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * part categories.
     *
     * @var PartCategoryManagementService
     */
    protected PartCategoryManagementService $management;

    /**
     * Service responsible for querying and listing part categories.
     *
     * @var PartCategoryQueryService
     */
    protected PartCategoryQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PartCategoryLogService $logger Handles audit logging for part
     * category events.
     * @param  PartCategoryManagementService $management Handles part category
     * create/update/delete/restore.
     * @param  PartCategoryQueryService $query Handles part category listing
     * and retrieval.
     */
    public function __construct(
        PartCategoryLogService $logger,
        PartCategoryManagementService $management,
        PartCategoryQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Part Category
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated part category data with pagination
     * metadata and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartCategory::class);

        $partCategories = $this->query->list($request);

        return response()->json($partCategories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePartCategoryRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePartCategoryRequest $request Validated request containing
     * part category data.
     *
     * @return JsonResponse The newly created part category, with HTTP 201
     * Created.
     */
    public function store(StorePartCategoryRequest $request): JsonResponse
    {
        $partCategory = $this->management->store($request);

        $user = $request->user();

        $this->logger->partCategoryCreated(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single part category by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  PartCategory $partCategory Route-model-bound part category
     * instance.
     *
     * @return JsonResponse The resolved part category resource.
     */
    public function show(PartCategory $partCategory): JsonResponse
    {
        $this->authorize('view', $partCategory);
        $this->authorize('access', $partCategory);

        $partCategory = $this->query->show($partCategory);

        return response()->json($partCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePartCategoryRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePartCategoryRequest $request Validated request containing
     * updated part category data.
     * @param  PartCategory $partCategory Route-model-bound part category
     * instance to update.
     *
     * @return JsonResponse The updated part category resource.
     */
    public function update(
        UpdatePartCategoryRequest $request,
        PartCategory $partCategory
    ): JsonResponse {
        $partCategory = $this->management->update($request, $partCategory);

        $user = $request->user();

        $this->logger->partCategoryUpdated(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * part category instance is still fully accessible during logging.
     *
     * @param  PartCategory $partCategory Route-model-bound part category
     * instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(PartCategory $partCategory): JsonResponse
    {
        $this->authorize('delete', $partCategory);

        $user = auth()->user();

        $this->logger->partCategoryDeleted(
            $user,
            $user->id,
            $partCategory,
        );

        $this->management->destroy($partCategory);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified part category from soft deletion.
     *
     * Looks up the part category including trashed records, then authorises
     * via the 'restore' policy. Returns 404 if the part category is not
     * currently soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted part category.
     *
     * @return JsonResponse The restored part category resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the part category is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $partCategory = PartCategory::withTrashed()->findOrFail($id);
        $this->authorize('restore', $partCategory);

        if (! $partCategory->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->partCategoryRestored(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory);
    }
}
