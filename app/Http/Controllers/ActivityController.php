<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Activity::with('user', 'subject')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Activity $activity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Activity $activity)
    {
        return response()->json($activity->load('user', 'subject'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'type' => 'required|string',
            'subject_type' => 'nullable|string',
            'subject_id' => 'nullable|integer',
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $activity = Activity::create($data);
        return response()->json($activity, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Activity $activity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Activity $activity)
    {
        $data = $request->validate([
            'type' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $activity->update($data);
        return response()->json($activity);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Activity $activity
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return response()->json(null, 204);
    }
}
