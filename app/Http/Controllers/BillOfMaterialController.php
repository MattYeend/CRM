<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Models\Product;
use App\Services\BillOfMaterials\BillOfMaterialLogService;
use App\Services\BillOfMaterials\BillOfMaterialManagementService;
use App\Services\BillOfMaterials\BillOfMaterialQueryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
     * @param  Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BillOfMaterial::class);

        $manufacturable = $this->resolveManufacturable($request);

        return response()->json(
            $this->query->list($request, $manufacturable)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreBillOfMaterialRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreBillOfMaterialRequest $request): JsonResponse
    {
        $manufacturable = $this->resolveManufacturable($request);

        $billOfMaterial = $this->management->store(
            $request,
            $manufacturable
        );

        $this->logger->billOfMaterialCreated(
            $request->user(),
            $request->user()->id,
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
        string $type,
        string $manufacturable,
        BillOfMaterial $billOfMaterial
    ): JsonResponse {
        $model = $this->resolveManufacturable($request);

        abort_unless(
            $billOfMaterial->manufacturable_id === $model->id &&
            $billOfMaterial->manufacturable_type === $model->getMorphClass(),
            404
        );
        $billOfMaterial = $this->management->update($request, $billOfMaterial);

        $this->logger->billOfMaterialUpdated(
            $request->user(),
            $request->user()->id,
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
        Request $request,
        string $type,
        string $manufacturable,
        BillOfMaterial $billOfMaterial
    ): JsonResponse {
        $this->authorize('delete', $billOfMaterial);
        $model = $this->resolveManufacturable($request);

        abort_unless(
            $billOfMaterial->manufacturable_id === $model->id &&
            $billOfMaterial->manufacturable_type === $model->getMorphClass(),
            404
        );

        $this->logger->billOfMaterialDeleted(
            $request->user(),
            $request->user()->id,
            $billOfMaterial,
        );

        $this->management->destroy($billOfMaterial);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * @param  Model $manufacturable
     * @param  int|string $id
     *
     * @return JsonResponse
     */
    public function restore(
        Request $request,
        string $type,
        string $manufacturable,
        string $id
    ): JsonResponse {
        $billOfMaterial = BillOfMaterial::withTrashed()->findOrFail($id);
        $model = $this->resolveManufacturable($request);

        abort_unless(
            $billOfMaterial->manufacturable_id === $model->id &&
            $billOfMaterial->manufacturable_type === $model->getMorphClass(),
            404
        );

        $this->authorize('restore', $billOfMaterial);

        if (! $billOfMaterial->trashed()) {
            abort(404);
        }

        $restored = $this->management->restore($id);

        $this->logger->billOfMaterialRestored(
            $request->user(),
            $request->user()->id,
            $restored,
        );

        return response()->json($restored);
    }

    /**
     * Resolve the manufacturable model from route parameters.
     */
    private function resolveManufacturable(Request $request): Model
    {
        $type = $request->route('type');
        $id = $request->route('manufacturable');

        return match ($type) {
            'part' => Part::findOrFail($id),
            'product' => Product::findOrFail($id),
            default => abort(404, 'Invalid manufacturable type'),
        };
    }
}
