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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Quote resource.
 *
 * Delegates business logic to four dedicated services:
 *   - QuoteLogService — records audit log entries for quote changes
 *   - QuoteManagementService — handles create, update, delete, and restore
 *      operations
 *   - QuoteQueryService — handles read/list queries with filtering and
 *      pagination
 *   - QuoteProductManagementService — handles adding, updating, removing, and
 *      restoring products on a quote
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class QuoteController extends Controller
{
    /**
     * Service responsible for writing audit log entries for quote events.
     *
     * @var QuoteLogService
     */
    protected QuoteLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * quotes.
     *
     * @var QuoteManagementService
     */
    protected QuoteManagementService $management;

    /**
     * Service responsible for querying and listing quotes.
     *
     * @var QuoteQueryService
     */
    protected QuoteQueryService $query;

    /**
     * Service responsible for managing the products associated with a quote.
     *
     * @var QuoteProductManagementService
     */
    protected QuoteProductManagementService $quoteProductManagement;

    /**
     * Inject the required services into the controller.
     *
     * @param QuoteLogService $logger Handles audit logging for quote events.
     *
     * @param QuoteManagementService $management Handles quote
     * create/update/delete/restore.
     *
     * @param QuoteQueryService $query Handles quote listing and retrieval.
     *
     * @param QuoteProductManagementService $quoteProductManagement Handles
     * product associations on a quote.
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
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated quote data.
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
     * Validation is handled upstream by StoreQuoteRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param StoreQuoteRequest $request Validated request containing quote
     * data.
     *
     * @return JsonResponse The newly created quote, with HTTP 201 Created.
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
     * Returns a single quote by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param Quote $quote Route-model-bound quote instance.
     *
     * @return JsonResponse The resolved quote resource.
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
     * Validation is handled upstream by UpdateQuoteRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param UpdateQuoteRequest $request Validated request containing updated
     * quote data.
     *
     * @param Quote $quote Route-model-bound quote instance to update.
     *
     * @return JsonResponse The updated quote resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * quote instance is still fully accessible during logging.
     *
     * @param Quote $quote Route-model-bound quote instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
     */
    public function destroy(Quote $quote): JsonResponse
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
     * Restore the specified quote from soft deletion.
     *
     * Looks up the quote including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the quote is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param int|string $id The primary key of the soft-deleted quote.
     *
     * @return JsonResponse The restored quote resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the quote is not trashed (404).
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
     * Attach products to the specified quote.
     *
     * Accepts a list of products from the request payload and delegates to
     * the quote product management service to associate them with the quote.
     *
     * @param Request $request Incoming HTTP request containing a 'products'
     * array.
     *
     * @param Quote $quote Route-model-bound quote instance to attach products
     * to.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function addProducts(Request $request, Quote $quote): JsonResponse
    {
        $items = $request->input('products');
        $this->quoteProductManagement->add($quote, $items);

        return response()->json(['message' => 'Products added to quote']);
    }

    /**
     * Update the products attached to the specified quote.
     *
     * Accepts a revised list of products from the request payload and
     * delegates to the quote product management service to apply the changes.
     *
     * @param Request $request Incoming HTTP request containing a 'products'
     * array.
     *
     * @param Quote $quote Route-model-bound quote instance whose product
     * associations should be updated.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function updateProducts(Request $request, Quote $quote): JsonResponse
    {
        $items = $request->input('products');
        $this->quoteProductManagement->update($quote, $items);

        return response()->json(['message' => 'Products updated for quote']);
    }

    /**
     * Remove a product from the specified quote.
     *
     * Delegates to the quote product management service to dissociate the
     * given product from the quote.
     *
     * @param Quote $quote Route-model-bound quote instance to remove the
     * product from.
     *
     * @param Product $product Route-model-bound product instance to remove.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function removeProduct(Quote $quote, Product $product): JsonResponse
    {
        $this->quoteProductManagement->remove($quote, $product->id);

        return response()->json(['message' => 'Product removed from quote']);
    }

    /**
     * Restore a previously removed product to the specified quote.
     *
     * Delegates to the quote product management service to re-associate the
     * given product with the quote.
     *
     * @param Quote $quote Route-model-bound quote instance to restore the
     * product to.
     *
     * @param Product $product Route-model-bound product instance to restore.
     *
     * @return JsonResponse Confirmation message on success.
     */
    public function restoreProduct(Quote $quote, Product $product): JsonResponse
    {
        $this->quoteProductManagement->restore($quote, $product->id);

        return response()->json(['message' => 'Product restored for quote']);
    }
}
