<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\Notes\NoteLogService;
use App\Services\Notes\NoteManagementService;
use App\Services\Notes\NoteQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Declare a protected property to hold the NoteLogService,
     * NoteManagementService and NoteQueryService instance
     *
     * @var NoteLogService
     * @var NoteManagementService
     * @var NoteQueryServic
     */
    protected NoteLogService $logger;
    protected NoteManagementService $management;
    protected NoteQueryService $query;

    /**
     * Constructor for the controller
     *
     * @param NoteLogService $logger
     *
     * @param NoteManagementService $management
     *
     * @param NoteQueryService $query
     *
     * An instance of the NoteLogService used for logging
     * note-related actions
     * An instance of the NoteManagementService for management
     * of notes
     * An instance of the NoteQueryService for the query of
     * note-related actions
     */
    public function __construct(
        NoteLogService $logger,
        NoteManagementService $management,
        NoteQueryService $query,
    ) {
        $this->logger = $logger;
        $this->management = $management;
        $this->query = $query;
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

        $note = $this->query->list($request);

        return response()->json($note);
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

        $note = $this->query->show($note);

        return response()->json($note);
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
        $note = $this->management->store($request);

        $user = $request->user();

        $this->logger->noteCreated(
            $user,
            $user->id,
            $note,
        );

        return response()->json($note, 201);
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
        $note = $this->management->update($request, $note);

        $user = $request->user();

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

        $this->management->destroy($note);

        return response()->json(null, 204);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        $note = $this->management->restore((int) $id);

        $this->authorize('restore', $note);

        $user = auth()->user();

        $this->logger->noteRestored(
            $user,
            $user->id,
            $note
        );

        return response()->json($note);
    }
}
