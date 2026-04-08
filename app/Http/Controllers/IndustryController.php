<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIndustryRequest;
use App\Http\Requests\UpdateIndustryRequest;
use App\Models\Industry;
use App\Services\Industries\IndustryLogService;
use App\Services\Industries\IndustryManagementService;
use App\Services\Industries\IndustryQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Industry resource.
 *
 * Delegates business logic to three dedicated services:
 *   - IndustryLogService — records audit log entries for industry changes
 *   - IndustryManagementService — handles create, update, delete, and
 *      restore operations
 *   - IndustryQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class IndustryController extends Controller
{
    /**
     * Service responsible for writing audit log entries for industry events.
     *
     * @var IndustryLogService
     */
    protected IndustryLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * industries.
     *
     * @var IndustryManagementService
     */
    protected IndustryManagementService $management;

    /**
     * Service responsible for querying and listing industries.
     *
     * @var IndustryQueryService
     */
    protected IndustryQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  IndustryLogService $logger Handles audit logging for
     * industry events.
     * @param  IndustryManagementService $management Handles industry
     * create/update/delete/restore.
     * @param  IndustryQueryService $query Handles industry listing and
     * retrieval.
     */
    public function __construct(
        IndustryLogService $logger,
        IndustryManagementService $management,
        IndustryQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Industry
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated industry data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Industry::class);

        $industry = $this->query->list($request);

        return response()->json($industry);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreIndustryRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreIndustryRequest $request Validated request containing
     * industry data.
     *
     * @return JsonResponse The newly created industry, with HTTP 201 Created.
     */
    public function store(StoreIndustryRequest $request): JsonResponse
    {
        $industry = $this->management->store($request);

        $user = $request->user();

        $this->logger->industryCreated(
            $user,
            $user->id,
            $industry,
        );

        return response()->json($industry, 201);
    }

    /**
     * Display the specified resource.
     *
     * Return a single industry by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Industry $industry Route-model-bound industry instance.
     *
     * @return JsonResponse The resolved industry resource.
     */
    public function show(Industry $industry): JsonResponse
    {
        $this->authorize('view', $industry);
        $this->authorize('access', $industry);

        $industry = $this->query->show($industry);

        return response()->json($industry);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateIndustryRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     *
     * @param  UpdateIndustryRequest $request Validated request containing
     * updated industry data.
     * @param  Industry $industry Route-model-bound industry instance
     * to update.
     *
     * @return JsonResponse The updated industry resource.
     */
    public function update(
        UpdateIndustryRequest $request,
        Industry $industry
    ): JsonResponse {
        $industry = $this->management->update($request, $industry);

        $user = $request->user();

        $this->logger->industryUpdated(
            $user,
            $user->id,
            $industry,
        );

        return response()->json($industry);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * industry instance is still fully accessible during logging.
     *
     * @param  Industry $industry Route-model-bound industry instance to
     * delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Industry $industry): JsonResponse
    {
        $this->authorize('delete', $industry);

        $user = auth()->user();

        $this->logger->industryDeleted(
            $user,
            $user->id,
            $industry,
        );

        $this->management->destroy($industry);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified user from soft deletion.
     *
     * Looks up the industry including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the industry is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted industry.
     *
     * @return JsonResponse The restored industry resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the industry is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $industry = Industry::withTrashed()->findOrFail($id);
        $this->authorize('restore', $industry);

        if (! $industry->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->industryRestored(
            $user,
            $user->id,
            $industry
        );

        return response()->json($industry);
    }
}
