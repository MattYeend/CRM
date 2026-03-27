<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;
use App\Services\Parts\PartLogService;
use App\Services\Parts\PartManagementService;
use App\Services\Parts\PartQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Declare a protected property to hold the PartLogService,
     * PartManagementService and PartQueryService instance
     *
     * @var PartLogService
     * @var PartManagementService
     * @var PartQueryService
     */
    protected PartLogService $logger;
    protected PartManagementService $management;
    protected PartQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PartLogService $logger
     *
     * @param PartManagementService $management
     *
     * @param PartQueryService $query
     *
     * An instance of the PartLogService used for logging
     * part-related actions
     * An instance of the PartManagementService for management
     * of parts
     * An instance of the PartQueryService for the query of
     * part-related actions
     */
    public function __construct(
        PartLogService $logger,
        PartManagementService $management,
        PartQueryService $query,
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
        $this->authorize('viewAny', Part::class);

        $part = $this->query->list($request);

        return response()->json($part);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePartRequest $request
     *
     * @return JsonResponse
     */
    public function store(StorePartRequest $request): JsonResponse
    {
        $part = $this->management->store($request);

        $user = $request->user();

        $this->logger->partCreated(
            $user,
            $user->id,
            $part,
        );

        return response()->json($part, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function show(Part $part): JsonResponse
    {
        $this->authorize('view', $part);

        $part = $this->query->show($part);

        return response()->json($part);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePartRequest $request
     *
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): JsonResponse {
        $part = $this->management->update($request, $part);

        $user = $request->user();

        $this->logger->partUpdated(
            $user,
            $user->id,
            $part,
        );

        return response()->json($part);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Part $part
     *
     * @return JsonResponse
     */
    public function destroy(Part $part): JsonResponse
    {
        $this->authorize('delete', $part);

        $user = auth()->user();

        $this->logger->partDeleted(
            $user,
            $user->id,
            $part,
        );

        $this->management->destroy($part);

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
        $part = Part::withTrashed()->findOrFail($id);
        $this->authorize('restore', $part);

        if (! $part->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->partRestored(
            $user,
            $user->id,
            $part
        );

        return response()->json($part);
    }
}
