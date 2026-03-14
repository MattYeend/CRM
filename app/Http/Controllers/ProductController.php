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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Declare a protected property to hold the ProductLogService,
     * ProductManagementService, ProductQueryService,
     * DealProductManagementService, OrderProductManagementService
     * and QuoteProductManagementService instance
     *
     * @var ProductLogService
     * @var ProductManagementService
     * @var ProductQueryService
     * @var DealProductManagementService
     * @var OrderProductManagementService
     * @var QuoteProductManagementService
     */
    protected ProductLogService $logger;
    protected ProductManagementService $management;
    protected ProductQueryService $query;
    protected DealProductManagementService $dealProductManagement;
    protected OrderProductManagementService $orderProductManagement;
    protected QuoteProductManagementService $quoteProductManagement;

    /**
     * Constructor for the controller
     *
     * @param ProductLogService $logger
     *
     * @param ProductManagementService $management
     *
     * @param ProductQueryService $query
     *
     * @param DealProductManagementService $dealProductManagement
     *
     * @param OrderProductManagementService $orderProductManagement
     *
     * @param QuoteProductManagementService $quoteProductManagement
     *
     * An instance of the ProductLogService used for logging
     * product-related actions
     * An instance of the ProductManagementService for management
     * of products
     * An instance of the ProductQueryService for the query of
     * product-related actions
     * An instance of the DealProductManagementService for the query
     * of deal product-related actions
     * An instance of the OrderProductManagementService for the query
     * of order product-related actions
     *  An instance of the QuoteProductManagementService for the query
     * of quote product-related actions
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $product = $this->query->list($request);

        return response()->json($product);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     *
     * @return JsonResponse
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
     * @param Product $product
     *
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        $product = $this->query->show($product);

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     *
     * @param Product $product
     *
     * @return JsonResponse
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
     * @param Product $product
     *
     * @return JsonResponse
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
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->authorize('restore', $product);
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
     * Attach deals to a product.
     *
     * @param Request $request
     *
     * @param Product $product
     *
     * @return JsonResponse
     *
     * $request->input('deals') => [
     *   ['deal_id' => 1, 'quantity' => 2, 'price' => 100, 'meta' => []],
     * ]
     */
    public function addDeals(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('deals');
        $this->management->addDeals($product->id, $items);

        return response()->json(['message' => 'Deals added to product']);
    }

    /**
     * Update pivot data for deals attached to a product.
     *
     * @param Request $request
     *
     * @param Product $product
     *
     * @return JsonResponse
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
     * Remove a deal from a product.
     *
     * @param int $id
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function removeDeal(int $id, Deal $deal): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $this->management->removeDeal($product->id, $deal->id);

        return response()->json(['message' => 'Deal removed from product']);
    }

    /**
     * Restore a previously removed deal on a product.
     *
     * @param Product $product
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function restoreDeal(Product $product, Deal $deal): JsonResponse
    {
        $this->management->restore($product->id, $deal->id);

        return response()->json(['message' => 'Deal restored for product']);
    }

    /**
     * Attach orders to a product.
     *
     * @param Request $request Request containing 'orders' array
     *
     * @param Product $product The product to attach orders to
     *
     * @return JsonResponse
     */
    public function addOrders(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('orders');
        $this->management->addOrders($product->id, $items);

        return response()->json(['message' => 'Orders added to product']);
    }

    /**
     * Update orders attached to a product.
     *
     * @param Request $request Request containing 'orders' array
     *
     * @param Product $product The product whose orders are being updated
     *
     * @return JsonResponse
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
     * Remove an order from a product.
     *
     * @param Product $product
     *
     * @param Order $order The order to remove
     *
     * @return JsonResponse
     */
    public function removeOrder(Product $product, Deal $deal): JsonResponse
    {
        $this->management->removeOrder($product->id, $deal->id);

        return response()->json(['message' => 'Deal removed from product']);
    }

    /**
     * Restore a previously removed order for a product.
     *
     * @param Product $product
     *
     * @param Order $order The order to restore
     *
     * @return JsonResponse
     */
    public function restoreOrder(Product $product, Order $order): JsonResponse
    {
        $this->management->restoreOrder($product->id, $order->id);

        return response()->json(['message' => 'Order restored for product']);
    }

    /**
     * Attach quotes to a product.
     *
     * @param Request $request Request containing 'quotes' array
     *
     * @param Product $product The product to attach quotes to
     *
     * @return JsonResponse
     */
    public function addQuotes(Request $request, Product $product): JsonResponse
    {
        $items = $request->input('quotes');
        $this->management->addQuotes($product->id, $items);

        return response()->json(['message' => 'Quotes added to product']);
    }

    /**
     * Update quotes attached to a product.
     *
     * @param Request $request Request containing 'quotes' array
     *
     * @param Product $product The product whose quotes are being updated
     *
     * @return JsonResponse
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
     * Remove a quote from a product.
     *
     * @param Product $product
     *
     * @param Quote $quote The quote to remove
     *
     * @return JsonResponse
     */
    public function removeQuote(Product $product, Deal $deal): JsonResponse
    {
        $this->management->removeQuote($product->id, $deal->id);

        return response()->json(['message' => 'Deal removed from product']);
    }

    /**
     * Restore a previously removed quote for a product.
     *
     * @param Product $product
     *
     * @param Quote $quote The quote to restore
     *
     * @return JsonResponse
     */
    public function restoreQuote(Product $product, Quote $quote): JsonResponse
    {
        $this->management->restoreQuote($product->id, $quote->id);

        return response()->json(['message' => 'Quote restored for product']);
    }
}
