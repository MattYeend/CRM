<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Http\Requests\UpdateBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Models\Part;
use App\Services\BillOfMaterials\BillOfMaterialLogService;
use App\Services\BillOfMaterials\BillOfMaterialManagementService;
use App\Services\BillOfMaterials\BillOfMaterialQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillOfMaterialController extends Controller
{
    /**
     * Declare a protected property to hold the BillOfMaterialLogService,
     * BillOfMaterialManagementService and BillOfMaterialQueryService instance
     *
     * @var BillOfMaterialLogService
     * @var BillOfMaterialManagementService
     * @var BillOfMaterialQueryService
     */
    protected BillOfMaterialLogService $logger;
    protected BillOfMaterialManagementService $management;
    protected BillOfMaterialQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param BillOfMaterialLogService $logger
     *
     * An instance of the BillOfMaterialLogService used for logging
     * bill of material-related actions
     *
     * @param BillOfMaterialManagementService $management
     *
     * An instance of the BillOfMaterialManagementService for management
     * of bill of materials
     *
     * @param BillOfMaterialQueryService $query
     *
     * An instance of the BillOfMaterialQueryService for the query of
     * bill of material-related actions
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
     * @param Part $part
     * @param Request $request
     *
     * @return JsonResponse
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
     * @param StoreBillOfMaterialRequest $request
     * @param Part $part
     *
     * @return JsonResponse
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
     * @param UpdateBillOfMaterialRequest $request
     * @param Part $part
     * @param BillOfMaterial $billOfMaterial
     *
     * @return JsonResponse
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
     * @param Part $part
     * @param BillOfMaterial $billOfMaterial
     *
     * @return JsonResponse
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
     * @param Part $part
     * @param int $id
     *
     * @return JsonResponse
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
