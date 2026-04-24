<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Deal;
use App\Models\Order;
use App\Models\Product;
use App\Models\Quote;
use App\Services\DealProducts\DealProductManagementService;
use App\Services\OrderProducts\OrderProductManagementService;
use App\Services\Products\ProductLogService;
use App\Services\Products\ProductManagementService;
use App\Services\Products\ProductQueryService;
use App\Services\QuoteProducts\QuoteProductManagementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Product resource.
 *
 * Delegates business logic to six dedicated services:
 *   - ProductLogService — records audit log entries for product changes
 *   - ProductManagementService — handles create, update, delete, and restore
 *      operations
 *   - ProductQueryService — handles read/list queries with filtering and
 *      pagination
 *   - DealProductManagementService — handles adding, updating, removing, and
 *      restoring deals associated with a product
 *   - OrderProductManagementService — handles adding, updating, removing, and
 *      restoring orders associated with a product
 *   - QuoteProductManagementService — handles adding, updating, removing, and
 *      restoring quotes associated with a product
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class ProductController extends Controller
{
    /**
     * Service responsible for writing audit log entries for product events.
     *
     * @var ProductLogService
     */
    protected ProductLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * products.
     *
     * @var ProductManagementService
     */
    protected ProductManagementService $management;

    /**
     * Service responsible for querying and listing products.
     *
     * @var ProductQueryService
     */
    protected ProductQueryService $query;

    /**
     * Service responsible for managing the deals associated with a product.
     *
     * @var DealProductManagementService
     */
    protected DealProductManagementService $dealProductManagement;

    /**
     * Service responsible for managing the orders associated with a product.
     *
     * @var OrderProductManagementService
     */
    protected OrderProductManagementService $orderProductManagement;

    /**
     * Service responsible for managing the quotes associated with a product.
     *
     * @var QuoteProductManagementService
     */
    protected QuoteProductManagementService $quoteProductManagement;

    /**
     * Inject the required services into the controller.
     *
     * @param ProductLogService $logger Handles audit logging for product
     * events.
     * @param ProductManagementService $management Handles product
     * create/update/delete/restore.
     * @param ProductQueryService $query Handles product listing and retrieval.
     * @param DealProductManagementService $dealProductManagement Handles deal
     * associations on a product.
     * @param OrderProductManagementService $orderProductManagement Handles
     * order associations on a product.
     * @param QuoteProductManagementService $quoteProductManagement Handles
     * quote associations on a product.
     */
    public function __construct(
        ProductLogService $logger,
        ProductManagementService $management,
        ProductQueryService $query,
        DealProductManagementService $dealProductManagement,
        OrderProductManagementService $orderProductManagement,
        QuoteProductManagementService $quoteProductManagement,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
        $this->dealProductManagement = $dealProductManagement;
        $this->orderProductManagement = $orderProductManagement;
        $this->quoteProductManagement = $quoteProductManagement;
    }

    /**
     * Display a listing of the resource.
     *
     * Also includes the authenticated user's permissions for the Product
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated product data with pagination
     * metadata and permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = $this->query->list($request);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreProductRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param StoreProductRequest $request Validated request containing
     * product data.
     *
     * @return JsonResponse The newly created product, with HTTP 201 Created.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->management->store($request);

        $user = $request->user();

        $this->logger->productCreated(
            $user,
            $user->id,
            $product,
        );

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     *
     * Returns a single product by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param Product $product Route-model-bound product instance.
     *
     * @return JsonResponse The resolved product resource.
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        $this->authorize('access', $product);

        $product = $this->query->show($product);

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateProductRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param UpdateProductRequest $request Validated request containing
     * updated product data.
     * @param Product $product Route-model-bound product instance to update.
     *
     * @return JsonResponse The updated product resource.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): JsonResponse {
        $product = $this->management->update($request, $product);

        $user = $request->user();

        $this->logger->productUpdated(
            $user,
            $user->id,
            $product,
        );

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * product instance is still fully accessible during logging.
     *
     * @param Product $product Route-model-bound product instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $user = auth()->user();

        $this->logger->productDeleted(
            $user,
            $user->id,
            $product,
        );

        $this->management->destroy($product);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified product from soft deletion.
     *
     * Looks up the product including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the product is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param int|string $id The primary key of the soft-deleted product.
     *
     * @return JsonResponse The restored product resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the product is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->authorize('restore', $product);

        if (! $product->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->productRestored(
            $user,
            $user->id,
            $product
        );

        return response()->json($product);
    }

    /**
     * Attach deals to the specified product.
     *
     * Accepts a list of deals from the request payload and delegates to the
     * product management service to associate them with the product.
     *
     * @param Request $request Incoming HTTP request containing a 'deals'
     * array, each entry with deal_id, quantity, price, and optional meta.
     * @param Product $product Route-model-bound product instance to attach
     * deals to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addDeals(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('deals');
        $this->management->addDeals($product->id, $items);

        return response()->json(['message' => 'Deals added to product']);
    }

    /**
     * Update the pivot data for deals attached to the specified product.
     *
     * Accepts a revised list of deals from the request payload and delegates
     * to the product management service to apply the changes.
     *
     * @param Request $request Incoming HTTP request containing a 'deals'
     * array.
     * @param Product $product Route-model-bound product instance whose deal
     * associations should be updated.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function updateDeals(
        Request $request,
        Product $product
    ): JsonResponse {
        $items = $request->input('deals');
        $this->management->updateDeals($product->id, $items);

        return response()->json(['message' => 'Deals updated for product']);
    }

    /**
     * Remove a deal from the specified product.
     *
     * Looks up the product including trashed records, then delegates to the
     * product management service to dissociate the given deal.
     *
     * @param int|string $id The primary key of the product, including trashed
     * records.
     * @param Deal $deal Route-model-bound deal instance to remove.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function removeDeal(int $id, Deal $deal): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->management->removeDeal($product->id, $deal->id);

        return response()->json(['message' => 'Deal removed from product']);
    }

    /**
     * Restore a previously removed deal on the specified product.
     *
     * Delegates to the product management service to re-associate the given
     * deal with the product.
     *
     * @param Product $product Route-model-bound product instance to restore
     * the deal to.
     * @param Deal $deal Route-model-bound deal instance to restore.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function restoreDeal(Product $product, Deal $deal): JsonResponse
    {
        $this->management->restore($product->id, $deal->id);

        return response()->json(['message' => 'Deal restored for product']);
    }

    /**
     * Attach orders to the specified product.
     *
     * Accepts a list of orders from the request payload and delegates to the
     * product management service to associate them with the product.
     *
     * @param Request $request Incoming HTTP request containing an 'orders'
     * array.
     * @param Product $product Route-model-bound product instance to attach
     * orders to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addOrders(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('orders');
        $this->management->addOrders($product->id, $items);

        return response()->json(['message' => 'Orders added to product']);
    }

    /**
     * Update the orders attached to the specified product.
     *
     * Accepts a revised list of orders from the request payload and delegates
     * to the product management service to apply the changes.
     *
     * @param Request $request Incoming HTTP request containing an 'orders'
     * array.
     * @param Product $product Route-model-bound product instance whose order
     * associations should be updated.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function updateOrders(
        Request $request,
        Product $product
    ): JsonResponse {
        $items = $request->input('orders');
        $this->management->updateOrders($product->id, $items);

        return response()->json(['message' => 'Orders updated for product']);
    }

    /**
     * Remove an order from the specified product.
     *
     * Delegates to the product management service to dissociate the given
     * order from the product.
     *
     * @param Product $product Route-model-bound product instance to remove
     * the order from.
     * @param Deal $deal Route-model-bound deal instance to remove.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function removeOrder(Product $product, Deal $deal): JsonResponse
    {
        $this->management->removeOrder($product->id, $deal->id);

        return response()->json(['message' => 'Deal removed from product']);
    }

    /**
     * Restore a previously removed order for the specified product.
     *
     * Delegates to the product management service to re-associate the given
     * order with the product.
     *
     * @param Product $product Route-model-bound product instance to restore
     * the order to.
     * @param Order $order Route-model-bound order instance to restore.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function restoreOrder(Product $product, Order $order): JsonResponse
    {
        $this->management->restoreOrder($product->id, $order->id);

        return response()->json(['message' => 'Order restored for product']);
    }

    /**
     * Attach quotes to the specified product.
     *
     * Accepts a list of quotes from the request payload and delegates to the
     * product management service to associate them with the product.
     *
     * @param Request $request Incoming HTTP request containing a 'quotes'
     * array.
     * @param Product $product Route-model-bound product instance to attach
     * quotes to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addQuotes(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('quotes');
        $this->management->addQuotes($product->id, $items);

        return response()->json(['message' => 'Quotes added to product']);
    }

    /**
     * Update the quotes attached to the specified product.
     *
     * Accepts a revised list of quotes from the request payload and delegates
     * to the product management service to apply the changes.
     *
     * @param Request $request Incoming HTTP request containing a 'quotes'
     * array.
     * @param Product $product Route-model-bound product instance whose quote
     * associations should be updated.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function updateQuotes(
        Request $request,
        Product $product
    ): JsonResponse {
        $items = $request->input('quotes');
        $this->management->updateQuotes($product->id, $items);

        return response()->json(['message' => 'Quotes updated for product']);
    }

    /**
     * Remove a quote from the specified product.
     *
     * Delegates to the product management service to dissociate the given
     * quote from the product.
     *
     * @param Product $product Route-model-bound product instance to remove
     * the quote from.
     * @param Quote $quote Route-model-bound quote instance to remove.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function removeQuote(Product $product, Quote $quote): JsonResponse
    {
        $this->management->removeQuote($product->id, $quote->id);

        return response()->json(['message' => 'Quote removed from product']);
    }

    /**
     * Restore a previously removed quote for the specified product.
     *
     * Delegates to the product management service to re-associate the given
     * quote with the product.
     *
     * @param Product $product Route-model-bound product instance to restore
     * the quote to.
     * @param Quote $quote Route-model-bound quote instance to restore.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function restoreQuote(Product $product, Quote $quote): JsonResponse
    {
        $this->management->restoreQuote($product->id, $quote->id);

        return response()->json(['message' => 'Quote restored for product']);
    }

    /**
     * Display a paginated stock-level listing of all products.
     *
     * Returns only the fields needed for stock management views:
     * id, name, sku, quantity, and reorder_point. Results are ordered
     * by quantity ascending so the lowest-stock products appear first.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * pagination params.
     *
     * @return JsonResponse Paginated product stock data.
     */
    public function stock(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::select('id', 'name', 'sku', 'quantity', 'reorder_point')
            ->orderBy('quantity')
            ->paginate(10)
            ->withQueryString();

        return response()->json($products);
    }

    /**
     * Display a paginated listing of products that are low on stock.
     *
     * Applies the lowStock() scope from the Product model, which filters
     * to products where quantity is at or below the reorder point.
     * Results are ordered by quantity ascending.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * pagination params.
     *
     * @return JsonResponse Paginated low-stock product data.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::select('id', 'name', 'sku', 'quantity', 'reorder_point')
            ->lowStock()
            ->orderBy('quantity')
            ->paginate(10)
            ->withQueryString();

        return response()->json($products);
    }
}
