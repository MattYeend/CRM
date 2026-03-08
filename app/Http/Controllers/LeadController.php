<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use App\Services\LeadLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Declare a protected property to hold the LeadLogService instance
     *
     * @var LeadLogService
     */
    protected LeadLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param LeadLogService $logger
     *
     * An instance of the LeadLogService used for logging
     * lead-related actions
     */
    public function __construct(LeadLogService $logger)
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
        $this->authorize('viewAny', Lead::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );
        $ownerId = $request->query('owner_id');

        $query = Lead::with([
            'owner',
            'assignedTo',
        ]);

        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * Display the specified resource.
     *
     * @param Lead $lead
     *
     * @return JsonResponse
     */
    public function show(Lead $lead): JsonResponse
    {
        $this->authorize('view', $lead);

        return response()->json(
            Lead::with($this->relations())->findOrFail($lead->id)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLeadRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;
        $data['owner_id'] = $data['owner_id'] ?? auth()->id();
        $data['created_by'] = auth()->id();

        $lead = Lead::create($data);

        $this->logger->leadCreated(
            $user,
            $user->id,
            $lead
        );

        return response()->json($lead, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLeadRequest $request
     *
     * @param Lead $lead
     *
     * @return JsonResponse
     */
    public function update(
        UpdateLeadRequest $request,
        Lead $lead
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $lead->update($data);

        $this->logger->leadUpdated(
            $user,
            $user->id,
            $lead
        );

        return response()->json($lead->fresh()->load($this->relations()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Lead $lead
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Lead $lead): JsonResponse
    {
        $this->authorize('delete', $lead);

        $user = auth()->user();
        $this->logger->leadDeleted(
            $user,
            $user->id,
            $lead,
        );

        $lead->update([
            'deleted_by' => $user->id,
        ]);

        $lead->delete();

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $lead = Lead::withTrashed()->findOrFail($id);
        $this->authorize('restore', $lead);

        $lead->restore();

        $this->logger->leadRestored(
            auth()->user(),
            auth()->id(),
            $lead
        );

        return response()->json($lead->fresh()->load($this->relations()));
    }

    /**
     * Get the relationships to load with the Lead model.
     *
     * @return array
     */
    private function relations(): array
    {
        return [
            'owner',
            'assignedTo',
        ];
    }
}
