<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Models\Deal;
use App\Services\DealLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /**
     * Declare a protected property to hold the ContactLogService instance
     *
     * @var DealLogService
     */
    protected DealLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param DealLogService $logger
     *
     * An instance of the DealLogService used for logging
     * deal-related actions
     */
    public function __construct(DealLogService $logger)
    {
        $this->logger = $logger;
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
        $this->authorize('viewAny', Deal::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );
        $status = $request->query('status');
        $ownerId = $request->query('owner_id');

        $query = Deal::with([
            'company',
            'contact',
            'owner',
            'pipeline',
            'stage',
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Display the specified resource.
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function show(Deal $deal): JsonResponse
    {
        $this->authorize('view', $deal);

        return response()->json($deal->load([
            'company',
            'contact',
            'owner',
            'pipeline',
            'stage',
            'notes',
            'tasks',
            'attachments',
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDealRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreDealRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $deal = Deal::create($data);

        $this->logger->dealCreated(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal->load($this->relations()), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDealRequest $request
     *
     * @param Deal $deal
     *
     * @return JsonResponse
     */
    public function update(
        UpdateDealRequest $request,
        Deal $deal
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $deal->update($data);

        $this->logger->dealUpdated(
            $user,
            $user->id,
            $deal,
        );

        return response()->json($deal->fresh()->load($this->relations()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Deal $deal
     *
     * @return \Illuminate\Http\JsonResponse
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

        $deal->update([
            'deleted_by' => $user->id,
        ]);
        $deal->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id): JsonResponse
    {
        $deal = Deal::withTrashed()->findOrFail($id);

        $this->authorize('restore', $deal);

        $user = auth()->user();

        $this->logger->dealRestored(
            $user,
            $user->id,
            $deal,
        );

        $deal->restore();

        return response()->json($deal);
    }

    /**
     * Default relations to eager load for show/index responses.
     *
     * @return array
     */
    private function relations(): array
    {
        return ['company', 'contact', 'owner', 'pipeline', 'stage'];
    }
}
