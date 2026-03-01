<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Services\DealLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Deal $deal)
    {
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
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'owner_id' => 'nullable|integer|exists:users,id',
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'stage_id' => 'nullable|integer|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
            'status' => 'nullable|in:open,won,lost,archived',
            'meta' => 'nullable|array',
        ]);

        $deal = Deal::create($data);

        $this->logger->dealCreated(
            auth()->user(),
            auth()->id(),
            $deal
        );

        return response()->json($deal->load($this->relations()), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param Deal $deal
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Deal $deal)
    {
        $data = $this->validateUpdateData($request);

        DB::transaction(function () use ($deal, $data) {
            $deal->update($data);
        });

        $this->logUpdate(
            $request,
            $deal
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
    public function destroy(Deal $deal)
    {
        $this->logger->dealDeleted(
            auth()->user(),
            auth()->id(),
            $deal
        );

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
    public function restore($id)
    {
        $deal = Deal::withTrashed()->findOrFail($id);

        $deal->restore();

        $this->logger->dealRestored(
            auth()->user(),
            auth()->id(),
            $deal
        );

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

    /**
     * Validate the update data for a deal.
     *
     * @param Request $request
     *
     * @return array
     */
    private function validateUpdateData(Request $request): array
    {
        return $request->validate([
            'title' => 'sometimes|required|string',
            'company_id' => 'nullable|integer|exists:companies,id',
            'contact_id' => 'nullable|integer|exists:contacts,id',
            'owner_id' => 'nullable|integer|exists:users,id',
            'pipeline_id' => 'nullable|integer|exists:pipelines,id',
            'stage_id' => 'nullable|integer|exists:pipeline_stages,id',
            'value' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'close_date' => 'nullable|date',
            'status' => 'nullable|in:open,won,lost,archived',
            'meta' => 'nullable|array',
        ]);
    }
    /**
     * Log the update of a deal.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Deal $deal
     *
     * @return void
     */
    private function logUpdate(Request $request, $deal): void
    {
        $this->logger->dealUpdated(
            $request->user(),
            auth()->id(),
            $deal
        );
    }
}
