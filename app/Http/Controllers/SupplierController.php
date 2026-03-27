<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\Suppliers\SupplierLogService;
use App\Services\Suppliers\SupplierManagementService;
use App\Services\Suppliers\SupplierQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Declare a protected property to hold the SupplierLogService,
     * SupplierManagementService and SupplierQueryService instance
     *
     * @var SupplierLogService
     * @var SupplierManagementService
     * @var SupplierQueryService
     */
    protected SupplierLogService $logger;
    protected SupplierManagementService $management;
    protected SupplierQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param SupplierLogService $logger
     *
     * @param SupplierManagementService $management
     *
     * @param SupplierQueryService $query
     *
     * An instance of the SupplierLogService used for logging
     * supplier-related actions
     * An instance of the SupplierManagementService for management
     * of suppliers
     * An instance of the SupplierQueryService for the query of
     * supplier-related actions
     */
    public function __construct(
        SupplierLogService $logger,
        SupplierManagementService $management,
        SupplierQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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
        $this->authorize('viewAny', Supplier::class);

        $part = $this->query->list($request);

        return response()->json($part);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSupplierRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->management->store($request);

        $user = $request->user();

        $this->logger->supplierCreated(
            $user,
            $user->id,
            $supplier,
        );

        return response()->json($supplier, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Supplier $supplier
     *
     * @return JsonResponse
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);

        $supplier = $this->query->show($supplier);

        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateSupplierRequest $request
     *
     * @param Supplier $supplier
     *
     * @return JsonResponse
     */
    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ): JsonResponse {
        $supplier = $this->management->update($request, $supplier);

        $user = $request->user();

        $this->logger->supplierUpdated(
            $user,
            $user->id,
            $supplier,
        );

        return response()->json($supplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Supplier $supplier
     *
     * @return JsonResponse
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);

        $user = auth()->user();

        $this->logger->supplierDeleted(
            $user,
            $user->id,
            $supplier,
        );

        $this->management->destroy($supplier);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $supplier = Supplier::withTrashed()->findOrFail($id);

        $this->authorize('restore', $supplier);
        
        if (! $supplier->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->supplierRestored(
            $user,
            $user->id,
            $supplier
        );

        return response()->json($supplier);
    }
}
