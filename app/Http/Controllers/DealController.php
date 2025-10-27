<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DealController extends Controller
{
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

        $query = Deal::with(['company', 'contact', 'owner', 'pipeline', 'stage']);

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
            'attachments'
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

        return response()->json($deal->load(['company','contact','owner','pipeline','stage']), 201);
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
        $data = $request->validate([
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

        DB::transaction(function() use ($deal, $data) {
            $deal->update($data);
        });

        return response()->json($deal->fresh()->load(['company','contact','owner','pipeline','stage']));
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
        return response()->json($deal);
    }
}
