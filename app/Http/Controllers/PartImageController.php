<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartImageRequest;
use App\Http\Requests\UpdatePartImageRequest;
use App\Models\PartImage;
use App\Services\PartImages\PartImageLogService;
use App\Services\PartImages\PartImageManagementService;
use App\Services\PartImages\PartImageQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartImageController extends Controller
{
    /**
     * Declare a protected property to hold the PartImageLogService,
     * PartImageManagementService and PartImageQueryService instance
     *
     * @var PartImageLogService
     * @var PartImageManagementService
     * @var PartImageQueryService
     */
    protected PartImageLogService $logger;
    protected PartImageManagementService $management;
    protected PartImageQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PartImageLogService $logger
     *
     * @param PartImageManagementService $management
     *
     * @param PartImageQueryService $query
     *
     * An instance of the PartImageLogService used for logging
     * part image-related actions
     * An instance of the PartImageManagementService for management
     * of part images
     * An instance of the PartImageQueryService for the query of
     * part image-related actions
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PartImage::class);

        $part = $this->query->list($request);

        return response()->json($part);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePartImageRequest $request
     *
     * @return JsonResponse
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
     * @param PartImage $partImage
     *
     * @return JsonResponse
     */
    public function show(PartImage $partImage): JsonResponse
    {
        $this->authorize('view', $partImage);

        $partImage = $this->query->show($partImage);

        return response()->json($partImage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePartImageRequest $request
     *
     * @param PartImage $partImage
     *
     * @return JsonResponse
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
     * @param PartImage $partImage
     *
     * @return JsonResponse
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
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $partImage = PartImage::withTrashed()->findOrFail($id);
        $this->authorize('restore', $partImage);
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
