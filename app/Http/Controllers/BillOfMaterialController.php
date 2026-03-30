<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Services\BillOfMaterials\BillOfMaterialLogService;
use App\Services\BillOfMaterials\BillOfMaterialManagementService;
use App\Services\BillOfMaterials\BillOfMaterialQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the BillOfMaterial resource.
 *
 * Delegates business logic to three dedicated services:
 *   - BillOfMaterialLogService — records audit log entries for bill of
 *      material changes
 *   - BillOfMaterialManagementService — handles create, update, delete,
 *      and restore operations
 *   - BillOfMaterialQueryService — handles read/list queries with
 *      filtering and pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class BillOfMaterialController extends Controller
{
    /**
     * Service responsible for writing audit log entries for bill of
     * material events.
     *
     * @var BillOfMaterialLogService
     */
    protected BillOfMaterialLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * bill of materials.
     *
     * @var BillOfMaterialManagementService
     */
    protected BillOfMaterialManagementService $management;

    /**
     * Service responsible for querying and listing bill of materials.
     *
     * @var BillOfMaterialQueryService
     */
    protected BillOfMaterialQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  BillOfMaterialLogService $logger Handles audit logging for
     * bill of material events.
     *
     * @param  BillOfMaterialManagementService $management Handles bill
     * of material create/update/delete/restore.
     *
     * @param  BillOfMaterialQueryService $query Handles bill of material
     * listing and retrieval.
     */
    public function __construct(
        BillOfMaterialLogService $logger,
        BillOfMaterialManagementService $management,
        BillOfMaterialQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the BOM
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Returns all bill of materials scoped to the given part.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Part $part Route-model-bound part instance to scope the listing.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse The list of bill of materials for the given part.
     */
    public function index(Part $part, Request $request): JsonResponse
    {
        $this->authorize('viewAny', BillOfMaterial::class);

        $billOfMaterials = $this->query->list($part, $request);

        return response()->json($billOfMaterials);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreBillOfMaterialRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreBillOfMaterialRequest $request Validated request containing
     * bill of material data.
     *
     * @param  Part $part Route-model-bound part instance
     * the bill of material belongs to.
     *
     * @return JsonResponse The newly created bill of material,
     * with HTTP 201 Created.
     */
    public function store(
        StoreBillOfMaterialRequest $request,
        Part $part
    ): JsonResponse {
        $billOfMaterial = $this->management->store($request, $part);

        $user = $request->user();

        $this->logger->billOfMaterialCreated(
            $user,
            $user->id,
            $billOfMaterial,
        );

        return response()->json($billOfMaterial, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateBillOfMaterialRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     *
     * @param  UpdateBillOfMaterialRequest $request Validated request containing
     * updated bill of material data.
     *
     * @param  Part $part Route-model-bound part instance
     * the bill of material belongs to.
     *
     * @param  BillOfMaterial $billOfMaterial Route-model-bound bill of material
     * instance to update.
     *
     * @return JsonResponse The updated bill of material resource.
     */
    public function update(
        UpdateBillOfMaterialRequest $request,
        Part $part,
        BillOfMaterial $billOfMaterial,
    ): JsonResponse {
        $billOfMaterial = $this->management->update($request, $billOfMaterial);

        $user = $request->user();

        $this->logger->billOfMaterialUpdated(
            $user,
            $user->id,
            $billOfMaterial,
        );

        return response()->json($billOfMaterial);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * bill of material instance is still fully accessible during logging.
     *
     * @param  Part $part Route-model-bound part instance
     * the bill of material belongs to.
     *
     * @param  BillOfMaterial $billOfMaterial Route-model-bound bill of material
     * instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(
        Part $part,
        BillOfMaterial $billOfMaterial
    ): JsonResponse {
        $this->authorize('delete', $billOfMaterial);

        $user = auth()->user();

        $this->logger->billOfMaterialDeleted(
            $user,
            $user->id,
            $billOfMaterial,
        );

        $this->management->destroy($billOfMaterial);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * Looks up the bill of material including trashed records, then authorises
     * via the 'restore' policy. Returns 404 if the bill of material is not
     * currently soft-deleted, preventing accidental double-restores.
     *
     * @param  Part $part Route-model-bound part instance the bill of material
     * belongs to.
     *
     * @param  int  $id The primary key of the soft-deleted bill of material.
     *
     * @return JsonResponse The restored bill of material resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the bill of material is not trashed (404).
     */
    public function restore(Part $part, int $id): JsonResponse
    {
        $billOfMaterial = BillOfMaterial::withTrashed()->findOrFail($id);
        $this->authorize('restore', $billOfMaterial);

        if (! $billOfMaterial->trashed()) {
            abort(404);
        }

        $this->management->restore($id);

        $user = auth()->user();

        $this->logger->billOfMaterialRestored(
            $user,
            $user->id,
            $billOfMaterial,
        );

        return response()->json($billOfMaterial);
    }
}
