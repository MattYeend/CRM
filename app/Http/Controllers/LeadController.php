<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\LeadLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Lead $lead): JsonResponse
    {
        return response()->json(
            Lead::with($this->relations())->findOrFail($lead->id)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
            'owner_id' => 'nullable|exists:users,id',
            'assigned_to' => 'nullable|exists:users,id',
            'meta' => 'nullable|array',
        ]);

        $data['owner_id'] = $data['owner_id'] ?? auth()->id();
        $data['created_by'] = auth()->id();

        $lead = Lead::create($data);

        $this->logger->leadCreated(
            auth()->user(),
            auth()->id(),
            $lead
        );

        return response()->json($lead, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @param Lead $lead
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|nullable|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'source' => 'sometimes|nullable|string|max:255',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'meta' => 'sometimes|nullable|array',
        ]);

        DB::transaction(function () use ($lead, $validated) {
            $lead->update(array_merge($validated, [
                'updated_by' => auth()->id(),
            ]));

            $this->logger->leadUpdated(
                auth()->user(),
                auth()->id(),
                $lead
            );
        });

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
        $this->logger->leadDeleted(
            auth()->user(),
            auth()->id(),
            $lead
        );

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
