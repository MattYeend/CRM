<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
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

        return response()->json(Note::with('user', 'noteable')->paginate($perPage));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Note $note
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Note $note)
    {
        return response()->json($note->load('user', 'noteable'));
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
            'notable_type' => 'nullable|string',
            'notable_id' => 'nullable|integer',
            'body' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        $note = Note::create($data);

        if (isset($data['notable_type']) && isset($data['notable_id'])) {
            try {
                $model = app($data['notable_type'])->find($data['notable_id']);
                if ($model) {
                    $model->notes()->save($note);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json($note->load('user','notable'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Note $note
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Note $note)
    {
        $data = $request->validate([
            'body' => 'sometimes|required|string',
            'meta' => 'nullable|array',
        ]);

        $note->update($data);
        return response()->json($note);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Note $note
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Note $note)
    {
        $note->delete();
        return response()->json(null, 204);
    }
}
