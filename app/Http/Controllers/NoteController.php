<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\NoteLogService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Declare a protected property to hold the NoteLogService instance
     *
     * @var NoteLogService
     */
    protected NoteLogService $logger;

    /**
     * Constructor for the controller
     *
     * @param NoteLogService $logger
     * An instance of the NoteLogService used for logging
     * note-related actions
     */
    public function __construct(NoteLogService $logger)
    {
        $this->logger = $logger;
    }

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
            Note::with('user', 'notable')->paginate($perPage)
        );
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
        return response()->json($note->load('user', 'notable'));
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

        $this->logger->noteCreated(
            $request->user(),
            $request->user()->id,
            $note
        );

        $this->attachNoteToModel($note, $data);

        return response()->json(
            $note->load('user', 'notable'),
            201
        );
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

        $this->logger->noteUpdated(
            $request->user(),
            $request->user()->id,
            $note
        );

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
        $this->logger->noteDeleted(
            auth()->user(),
            auth()->id(),
            $note
        );

        $note->delete();

        return response()->json(null, 204);
    }

    /**
     * Attach the note to the appropriate polymorphic model.
     *
     * @param \App\Models\Note $note
     *
     * @param array $data
     *
     * @return void
     */
    protected function attachNoteToModel(Note $note, array $data): void
    {
        if (! isset($data['notable_type'], $data['notable_id'])) {
            return;
        }

        try {
            $model = app($data['notable_type'])->find($data['notable_id']);
            if ($model) {
                $model->notes()->save($note);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
