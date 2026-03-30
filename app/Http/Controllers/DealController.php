<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Models\Product;
use App\Services\DealProducts\DealProductManagementService;
use App\Services\Deals\DealLogService;
use App\Services\Deals\DealManagementService;
use App\Services\Deals\DealQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Deal resource.
 *
 * Delegates business logic to four dedicated services:
 *   - DealLogService — records audit log entries for deal changes
 *   - DealManagementService — handles create, update, delete, and restore
 *      operations
 *   - DealQueryService — handles read/list queries with filtering and
 *      pagination
 *   - DealProductManagementService — handles attaching, updating, and removing
 *      products on deals
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */

class DealController extends Controller
{
    /**
     * Service responsible for writing audit log entries for deal events.
     *
     * @var DealLogService
     */
    protected DealLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * deals.
     *
     * @var DealManagementService
     */
    protected DealManagementService $management;

    /**
     * Service responsible for querying and listing deals.
     *
     * @var DealQueryService
     */
    protected DealQueryService $query;

    /**
     * Service responsible for managing the many-to-many relationship
     * between deals and products.
     *
     * @var DealProductManagementService
     */
    protected DealProductManagementService $dealProductManagement;

    /**
     * Inject the required services into the controller.
     *
     * @param  DealLogService $logger Handles audit logging for deal events.
     *
     * @param  DealManagementService $management Handles deal
     * create/update/delete/restore.
     *
     * @param  DealQueryService $query Handles deal listing and
     * retrieval.
     *
     * @param  DealProductManagementService $dealProductManagement
     * Handles attaching, updating, and removing products on deals.
     */
    public function __construct(
        DealLogService $logger,
        DealManagementService $management,
        DealQueryService $query,
        DealProductManagementService $dealProductManagement
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
        $this->dealProductManagement = $dealProductManagement;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Deal
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse The list of deals.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Deal::class);

        $deal = $this->query->list($request);

        return response()->json($deal);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreDealRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreDealRequest $request Validated request containing deal data.
     *
     * @return JsonResponse The newly created deal, with HTTP 201 Created.
     */
    public function store(StoreDealRequest $request): JsonResponse
    {
        $deal = $this->management->store($request);

        $user = $request->user();

        $this->logger->dealCreated(
            $user,
            $user->id,
            $deal
        );

        return response()->json($deal, 201);
    }

    /**
     * Display the specified resource.
     *
     * Return a single deal by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Deal $deal Route-model-bound deal instance.
     *
     * @return JsonResponse The resolved deal resource.
     */
    public function show(Deal $deal): JsonResponse
    {
        $this->authorize('view', $deal);

        $deal = $this->query->show($deal);

        return response()->json($deal);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateDealRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the authenticated
     * user.
     *
     * @param  UpdateDealRequest $request Validated request containing updated
     * deal data.
     *
     * @param  Deal $deal Route-model-bound deal instance to
     * update.
     *
     * @return JsonResponse The updated deal resource.
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): JsonResponse {
        $deal = $this->management->update($request, $deal);

        $user = $request->user();

        $this->logger->dealUpdated(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * deal instance is still fully accessible during logging.
     *
     * @param  Deal $deal Route-model-bound deal instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Deal $deal): JsonResponse
    {
        $this->authorize('delete', $deal);

        $user = auth()->user();

        $this->logger->dealDeleted(
            $user,
            $user->id,
            $deal,
        );

        $this->management->destroy($deal);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from soft deletion.
     *
     * Looks up the deal including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the deal is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted deal.
     *
     * @return JsonResponse The restored deal resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the deal is not trashed (404).
     */
    public function restore($id): JsonResponse
    {
        $deal = Deal::withTrashed()->findOrFail($id);
        $this->authorize('restore', $deal);

        if (! $deal->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->dealRestored(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal);
    }

    /**
     * Attach one or more products to a deal.
     *
     * Expects a 'products' array in the request body. Each item in the array
     * should contain:
     *   - 'product_id' (int) — the product to attach
     *   - 'quantity' (int, optional) — defaults to 1
     *   - 'price' (float, optional) — defaults to 0
     *   - 'meta' (array, optional) — any additional metadata
     *
     * @param  Request $request The HTTP request containing the
     * 'products' array.
     *
     * @param  Deal $deal Route-model-bound deal instance to attach products to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addProducts(Request $request, Deal $deal): JsonResponse
    {
        $items = $request->input('products');
        $this->dealProductManagement->add($deal, $items);
        return response()->json(['message' => 'Products added to deal']);
    }

    /**
     * Update the pivot data for products already attached to a deal.
     *
     * Expects a 'products' array in the request body. Each item in the array
     * should contain:
     *   - 'product_id' (int) — the product to update
     *   - 'quantity' (int, optional) — updated quantity
     *   - 'price' (float, optional) — updated price
     *   - 'meta' (array, optional) — updated metadata
     *
     * @param  Request $request The HTTP request containing the
     * 'products' array.
     *
     * @param  Deal $deal Route-model-bound deal instance whose products
     * will be updated.
     *
     * @return void
     */
    public function updateProducts(Request $request, Deal $deal): void
    {
        $items = $request->input('products');
        $this->dealProductManagement->update($deal, $items);
    }

    /**
     * Remove a product from a deal.
     *
     * Detaches the given product from the deal's product list via the
     * pivot table.
     *
     * @param  Deal $deal Route-model-bound deal instance to remove the
     * product from.
     *
     * @param  Product $product Route-model-bound product instance to detach.
     *
     * @return void
     */
    public function removeProduct(Deal $deal, Product $product): void
    {
        $this->dealProductManagement->remove($deal, $product->id);
    }

    /**
     * Restore a previously removed product on a deal.
     *
     * Re-attaches a product that was previously detached from the deal's
     * product list via the pivot table.
     *
     * @param  Deal $deal Route-model-bound deal instance to restore the
     * product on.
     *
     * @param  Product $product Route-model-bound product instance to restore.
     *
     * @return void
     */
    public function restoreProduct(Deal $deal, Product $product): void
    {
        $this->dealProductManagement->restore($deal, $product->id);
    }
}
