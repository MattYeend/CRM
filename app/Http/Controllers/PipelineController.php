<?php

namespace App\Http\Controllers;

use App\Models\Pipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
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
        return response()->json(
            Pipeline::with('stages')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Pipeline $pipeline
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Pipeline $pipeline)
    {
        return response()->json($pipeline->load('stages'));
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
            'name' => 'required|string',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $pipeline = Pipeline::create($data);
        return response()->json($pipeline, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Pipeline $pipeline)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'is_default' => 'nullable|boolean',
        ]);

        $pipeline->update($data);
        return response()->json($pipeline);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Pipeline $pipeline
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Pipeline $pipeline)
    {
        $pipeline->delete();
        return response()->json(null, 204);
    }
}
