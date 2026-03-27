<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Models\Product;
use App\Models\Quote;
use App\Services\QuoteProducts\QuoteProductManagementService;
use App\Services\Quotes\QuoteLogService;
use App\Services\Quotes\QuoteManagementService;
use App\Services\Quotes\QuoteQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    /**
     * Declare a protected property to hold the QuoteLogService,
     * QuoteManagementService, QuoteQueryService and
     * QuoteProductManagementService instance
     *
     * @var QuoteLogService
     * @var QuoteManagementService
     * @var QuoteQueryService
     * @var QuoteProductManagementService
     */
    protected QuoteLogService $logger;
    protected QuoteManagementService $management;
    protected QuoteQueryService $query;
    protected QuoteProductManagementService $quoteProductManagement;

    /**
     * Constructor for the controller
     *
     * @param QuoteLogService $logger
     *
     * @param QuoteManagementService $management
     *
     * @param QuoteQueryService $query
     *
     * @param QuoteProductManagementService $quoteProductManagement
     *
     * An instance of the QuoteLogService used for logging
     * quote-related actions
     * An instance of the QuoteManagementService for management
     * of quotes
     * An instance of the QuoteQueryService for the query of
     * quote-related actions
     * An instance of the QuoteProductManagementService for the query of
     * quote product-related actions
     */
    public function __construct(
        QuoteLogService $logger,
        QuoteManagementService $management,
        QuoteQueryService $query,
        QuoteProductManagementService $quoteProductManagement,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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
        $this->authorize('viewAny', Quote::class);

        $quote = $this->query->list($request);

        return response()->json($quote);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreQuoteRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        $quote = $this->management->store($request);

        $user = $request->user();

        $this->logger->quoteCreated(
            $user,
            $user->id,
            $quote,
        );

        return response()->json($quote, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Quote $quote
     *
     * @return JsonResponse
     */
    public function show(Quote $quote): JsonResponse
    {
        $this->authorize('view', $quote);

        $quote = $this->query->show($quote);

        return response()->json($quote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateQuoteRequest $request
     *
     * @param Quote $quote
     *
     * @return JsonResponse
     */
    public function update(
        UpdateQuoteRequest $request,
        Quote $quote
    ): JsonResponse {
        $quote = $this->management->update($request, $quote);

        $user = $request->user();

        $this->logger->quoteUpdated(
            $user,
            $user->id,
            $quote,
        );

        return response()->json($quote);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Quote $quote
     *
     * @return JsonResponse
     */
    public function destroy(Quote $quote)
    {
        $this->authorize('delete', $quote);

        $user = auth()->user();

        $this->logger->quoteDeleted(
            $user,
            $user->id,
            $quote,
        );

        $this->management->destroy($quote);

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
        $quote = Quote::withTrashed()->findOrFail($id);
        $this->authorize('restore', $quote);

        if (! $quote->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->quoteRestored(
            $user,
            $user->id,
            $quote
        );

        return response()->json($quote);
    }

    /**
     * Attach products to a quote.
     *
     * @param Request $request
     *
     * @param Quote $quote
     *
     * @return Json Response
     */
    public function addProducts(Request $request, Quote $quote): JsonResponse
    {
        $items = $request->input('products');
        $this->quoteProductManagement->add($quote, $items);

        return response()->json(['message' => 'Products added to quote']);
    }

    /**
     * Update products attached to a quote.
     *
     * @param Request $request
     *
     * @param Quote $quote
     *
     * @return Json Response
     */
    public function updateProducts(Request $request, Quote $quote): JsonResponse
    {
        $items = $request->input('products');
        $this->quoteProductManagement->update($quote, $items);

        return response()->json(['message' => 'Products updated for quote']);
    }

    /**
     * Remove a product from a quote.
     *
     * @param Request $request
     *
     * @param Quote $quote
     *
     * @return Json Response
     */
    public function removeProduct(Quote $quote, Product $product): JsonResponse
    {
        $this->quoteProductManagement->remove($quote, $product->id);

        return response()->json(['message' => 'Product removed from quote']);
    }

    /**
     * Restore a product previously removed from a quote.
     *
     * @param Request $request
     *
     * @param Quote $quote
     *
     * @return Json Response
     */
    public function restoreProduct(Quote $quote, Product $product): JsonResponse
    {
        $this->quoteProductManagement->restore($quote, $product->id);

        return response()->json(['message' => 'Product restored for quote']);
    }
}
