<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartSerialNumberRequest;
use App\Http\Requests\UpdatePartSerialNumberRequest;
use App\Models\Part;
use App\Models\PartSerialNumber;
use App\Services\PartSerialNumbers\PartSerialNumberLogService;
use App\Services\PartSerialNumbers\PartSerialNumberManagementService;
use App\Services\PartSerialNumbers\PartSerialNumberQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartSerialNumberController extends Controller
{
    /**
     * Declare a protected property to hold the PartSerialNumberLogService,
     * PartSerialNumberManagementService and PartSerialNumberQueryService
     * instance
     *
     * @var PartSerialNumberLogService
     * @var PartSerialNumberManagementService
     * @var PartSerialNumberQueryService
     */
    protected PartSerialNumberLogService $logger;
    protected PartSerialNumberManagementService $management;
    protected PartSerialNumberQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PartSerialNumberLogService $logger
     *
     * @param PartSerialNumberManagementService $management
     *
     * @param PartSerialNumberQueryService $query
     *
     * An instance of the PartSerialNumberLogService used for logging
     * part serial number-related actions
     * An instance of the PartSerialNumberManagementService for management
     * of part serial numbers
     * An instance of the PartSerialNumberQueryService for the query of
     * part serial number-related actions
     */
    public function __construct(
        PartSerialNumberLogService $logger,
        PartSerialNumberManagementService $management,
        PartSerialNumberQueryService $query,
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
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function index(Request $request, Part $part): JsonResponse
    {
        $this->authorize('viewAny', PartSerialNumber::class);

        $serials = $this->query->list($request, $part);

        return response()->json($serials);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePartSerialNumberRequest $request
     *
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function store(
        StorePartSerialNumberRequest $request,
        Part $part
    ): JsonResponse {
        $serial = $this->management->store($request, $part);

        $user = $request->user();

        $this->logger->partSerialNumberCreated(
            $user,
            $user->id,
            $serial,
        );

        return response()->json($serial, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePartSerialNumberRequest $request
     *
     * @param Part $part
     *
     * @param PartSerialNumber $serialNumber
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePartSerialNumberRequest $request,
        Part $part,
        PartSerialNumber $serialNumber
    ): JsonResponse {
        $serialNumber = $this->management->update($request, $serialNumber);

        $user = $request->user();

        $this->logger->partSerialNumberUpdated(
            $user,
            $user->id,
            $serialNumber,
        );

        return response()->json($serialNumber);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Part $part
     *
     * @param PartSerialNumber $serialNumber
     *
     * @return JsonResponse
     */
    public function destroy(
        Part $part,
        PartSerialNumber $serialNumber
    ): JsonResponse {
        $this->authorize('delete', $serialNumber);

        $user = auth()->user();

        $this->logger->partSerialNumberDeleted(
            $user,
            $user->id,
            $serialNumber,
        );

        $this->management->destroy($serialNumber);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $serialNumber = PartSerialNumber::withTrashed()->findOrFail($id);
        $this->authorize('restore', $serialNumber);

        if (! $serialNumber->trashed()) {
            abort(404);
        }

        $this->management->restore($id);

        $user = auth()->user();

        $this->logger->partSerialNumberRestored(
            $user,
            $user->id,
            $serialNumber,
        );

        return response()->json($serialNumber);
    }
}
