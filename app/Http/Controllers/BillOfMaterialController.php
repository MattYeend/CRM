<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Services\BillOfMaterials\BillOfMaterialLogService;
use App\Services\BillOfMaterials\BillOfMaterialManagementService;
use App\Services\BillOfMaterials\BillOfMaterialQueryService;
use Illuminate\Database\Eloquent\Model;
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
     * @param  BillOfMaterialManagementService $management Handles bill
     * of material create/update/delete/restore.
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
     * @param  Model $manufacturable Route-model-bound manufacturable instance.
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function index(Model $manufacturable, Request $request): JsonResponse
    {
        $this->authorize('viewAny', BillOfMaterial::class);

        $billOfMaterials = $this->query->list($manufacturable, $request);

        return response()->json($billOfMaterials);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreBillOfMaterialRequest $request
     * @param  Model $manufacturable
     *
     * @return JsonResponse
     */
    public function store(
        StoreBillOfMaterialRequest $request,
        Model $manufacturable
    ): JsonResponse {
        $billOfMaterial = $this->management->store($request, $manufacturable);

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
     * @param  UpdateBillOfMaterialRequest $request
     * @param  Model $manufacturable
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return JsonResponse
     */
    public function update(
        UpdateBillOfMaterialRequest $request,
        Model $manufacturable,
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
     * @param  Model $manufacturable
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return JsonResponse
     */
    public function destroy(
        Model $manufacturable,
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
     * @param  Model $manufacturable
     * @param  int  $id
     *
     * @return JsonResponse
     */
    public function restore(Model $manufacturable, int $id): JsonResponse
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
