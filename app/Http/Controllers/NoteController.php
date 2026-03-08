<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\NoteLogService;
use Illuminate\Http\JsonResponse;
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
     *
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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Note::class);

        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        return response()->json(
            Note::with('user', 'notable')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Note $note
     *
     * @return JsonResponse
     */
    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);

        return response()->json($note->load('user', 'notable'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNoteRequest $request
     *
     * @return JsonResponse
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['created_by'] = $user->id;

        $note = Note::create($data);

        $this->logger->noteCreated(
            $user,
            $user->id,
            $note,
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
     * @param UpdateNoteRequest $request
     *
     * @param Note $note
     *
     * @return JsonResponse
     */
    public function update(
        UpdateNoteRequest $request,
        Note $note
    ): JsonResponse {
        $user = $request->user();
        $data = $request->validated();
        $data['updated_by'] = $user->id;

        $note->update($data);

        $this->logger->noteUpdated(
            $user,
            $user->id,
            $note,
        );

        return response()->json($note);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Note $note
     *
     * @return JsonResponse
     */
    public function destroy(Note $note): JsonResponse
    {
        $this->authorize('delete', $note);

        $user = auth()->user();

        $this->logger->noteDeleted(
            $user,
            $user->id,
            $note,
        );

        $note->update([
            'deleted_by' => $user->id,
        ]);

        $note->delete();

        return response()->json(null, 204);
    }

    /**
     * Attach the note to the appropriate polymorphic model.
     *
     * @param Note $note
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
