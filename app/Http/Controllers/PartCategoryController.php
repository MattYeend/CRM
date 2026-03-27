<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartCategoryRequest;
use App\Http\Requests\UpdatePartCategoryRequest;
use App\Models\PartCategory;
use App\Services\PartCategories\PartCategoryLogService;
use App\Services\PartCategories\PartCategoryManagementService;
use App\Services\PartCategories\PartCategoryQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartCategoryController extends Controller
{
    /**
     * Declare a protected property to hold the PartCategoryLogService,
     * PartCategoryManagementService and PartCategoryQueryService instance
     *
     * @var PartCategoryLogService
     * @var PartCategoryManagementService
     * @var PartCategoryQueryService
     */
    protected PartCategoryLogService $logger;
    protected PartCategoryManagementService $management;
    protected PartCategoryQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param PartCategoryLogService $logger
     *
     * @param PartCategoryManagementService $management
     *
     * @param PartCategoryQueryService $query
     *
     * An instance of the PartCategoryLogService used for logging
     * part-related actions
     * An instance of the PartCategoryManagementService for management
     * of parts
     * An instance of the PartCategoryQueryService for the query of
     * part-related actions
     */
    public function __construct(
        PartCategoryLogService $logger,
        PartCategoryManagementService $management,
        PartCategoryQueryService $query,
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
        $this->authorize('viewAny', PartCategory::class);

        $part = $this->query->list($request);

        return response()->json($part);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePartCategoryRequest $request
     *
     * @return JsonResponse
     */
    public function store(StorePartCategoryRequest $request): JsonResponse
    {
        $partCategory = $this->management->store($request);

        $user = $request->user();

        $this->logger->partCategoryCreated(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param PartCategory $partCategory
     *
     * @return JsonResponse
     */
    public function show(PartCategory $partCategory): JsonResponse
    {
        $this->authorize('view', $partCategory);

        $partCategory = $this->query->show($partCategory);

        return response()->json($partCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePartCategoryRequest $request
     *
     * @param PartCategory $partCategory
     *
     * @return JsonResponse
     */
    public function update(
        UpdatePartCategoryRequest $request,
        PartCategory $partCategory
    ): JsonResponse {
        $partCategory = $this->management->update($request, $partCategory);

        $user = $request->user();

        $this->logger->partCategoryUpdated(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PartCategory $partCategory
     *
     * @return JsonResponse
     */
    public function destroy(PartCategory $partCategory): JsonResponse
    {
        $this->authorize('delete', $partCategory);

        $user = auth()->user();

        $this->logger->partCategoryDeleted(
            $user,
            $user->id,
            $partCategory,
        );

        $this->management->destroy($partCategory);

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
        $partCategory = PartCategory::withTrashed()->findOrFail($id);
        $this->authorize('restore', $partCategory);
        if (! $partCategory->trashed()) {
            abort(404);
        }
        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->partCategoryRestored(
            $user,
            $user->id,
            $partCategory,
        );

        return response()->json($partCategory);
    }
}
