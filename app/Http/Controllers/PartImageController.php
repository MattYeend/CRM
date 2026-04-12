<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartImageRequest;
use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;
use App\Services\PartImages\PartImageLogService;
use App\Services\PartImages\PartImageManagementService;
use App\Services\PartImages\PartImageQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the PartImage resource.
 *
 * Delegates business logic to three dedicated services:
 *   - PartImageLogService — records audit log entries for part image changes
 *   - PartImageManagementService — handles create, update, delete, and restore
 *      operations
 *   - PartImageQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class PartImageController extends Controller
{
    /**
     * Service responsible for writing audit log entries for part image events.
     *
     * @var PartImageLogService
     */
    protected PartImageLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * part images.
     *
     * @var PartImageManagementService
     */
    protected PartImageManagementService $management;

    /**
     * Service responsible for querying and listing part images.
     *
     * @var PartImageQueryService
     */
    protected PartImageQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  PartImageLogService $logger Handles audit logging for part image
     * events.
     * @param  PartImageManagementService $management Handles part image
     * create/update/delete/restore.
     * @param  PartImageQueryService $query Handles part image listing and
     * retrieval.
     */
    public function __construct(
        PartImageLogService $logger,
        PartImageManagementService $management,
        PartImageQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Part Image
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated part image data with pagination
     * metadata and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartImage::class);

        $partImages = $this->query->list($request);

        return response()->json($partImages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StorePartImageRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StorePartImageRequest $request Validated request containing
     * part image data.
     *
     * @return JsonResponse The newly created part image, with HTTP 201 Created.
     */
    public function store(StorePartImageRequest $request): JsonResponse
    {
        $partImage = $this->management->store($request);

        $user = $request->user();

        $this->logger->partImageCreated(
            $user,
            $user->id,
            $partImage,
        );

        return response()->json($partImage, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single part image by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  PartImage $partImage Route-model-bound part image instance.
     *
     * @return JsonResponse The resolved part image resource.
     */
    public function show(PartImage $partImage): JsonResponse
    {
        $this->authorize('view', $partImage);
        $this->authorize('access', $partImage);

        $partImage = $this->query->show($partImage);

        return response()->json($partImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdatePartImageRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdatePartImageRequest $request Validated request containing
     * updated part image data.
     * @param  PartImage $partImage Route-model-bound part image instance to
     * update.
     *
     * @return JsonResponse The updated part image resource.
     */
    public function update(
        UpdatePartImageRequest $request,
        PartImage $partImage
    ): JsonResponse {
        $partImage = $this->management->update($request, $partImage);

        $user = $request->user();

        $this->logger->partImageUpdated(
            $user,
            $user->id,
            $partImage,
        );

        return response()->json($partImage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * part image instance is still fully accessible during logging.
     *
     * @param  PartImage $partImage Route-model-bound part image instance to
     * delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(PartImage $partImage): JsonResponse
    {
        $this->authorize('delete', $partImage);

        $user = auth()->user();

        $this->logger->partImageDeleted(
            $user,
            $user->id,
            $partImage,
        );

        $this->management->destroy($partImage);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified part image from soft deletion.
     *
     * Looks up the part image including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the part image is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted part image.
     *
     * @return JsonResponse The restored part image resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the part image is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $partImage = PartImage::withTrashed()->findOrFail($id);
        $this->authorize('restore', $partImage);

        if (! $partImage->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->partImageRestored(
            $user,
            $user->id,
            $partImage,
        );

        return response()->json($partImage);
    }
}
