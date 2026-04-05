<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use App\Services\Notes\NoteLogService;
use App\Services\Notes\NoteManagementService;
use App\Services\Notes\NoteQueryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles HTTP requests for the Note resource.
 *
 * Delegates business logic to three dedicated services:
 *   - NoteLogService — records audit log entries for note changes
 *   - NoteManagementService — handles create, update, delete, and restore
 *      operations
 *   - NoteQueryService — handles read/list queries with filtering and
 *      pagination
 *
 * All responses are returned as JSON, making this controller suitable
 * for consumption by the Vue frontend or any API client.
 */
class NoteController extends Controller
{
    /**
     * Service responsible for writing audit log entries for note events.
     *
     * @var NoteLogService
     */
    protected NoteLogService $logger;

    /**
     * Service responsible for creating, updating, deleting, and restoring
     * notes.
     *
     * @var NoteManagementService
     */
    protected NoteManagementService $management;

    /**
     * Service responsible for querying and listing notes.
     *
     * @var NoteQueryService
     */
    protected NoteQueryService $query;

    /**
     * Inject the required services into the controller.
     *
     * @param  NoteLogService $logger Handles audit logging for note events.
     * @param  NoteManagementService $management Handles note
     * create/update/delete/restore.
     * @param  NoteQueryService $query Handles note listing and retrieval.
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
     * Also includes the authenticated user's permissions for the Note
     * resource, so the frontend can conditionally render create/view controls.
     *
     * Authorises via the 'viewAny' policy before returning data.
     *
     * @param  Request $request Incoming HTTP request; may carry
     * filter/pagination params.
     *
     * @return JsonResponse Paginated note data with pagination metadata and
     * permissions.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Note::class);

        $note = $this->query->list($request);

        return response()->json($note);
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation is handled upstream by StoreNoteRequest.
     *
     * After storing, an audit log entry is written against the authenticated
     * user.
     *
     * @param  StoreNoteRequest $request Validated request containing note data.
     *
     * @return JsonResponse The newly created note, with HTTP 201 Created.
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
     * Display the specified resource.
     *
     * Returns a single note by its model binding.
     *
     * Authorises via the 'view' policy before returning data.
     *
     * @param  Note $note Route-model-bound note instance.
     *
     * @return JsonResponse The resolved note resource.
     */
    public function show(Note $note): JsonResponse
    {
        $this->authorize('view', $note);
        $this->authorize('access', $note);

        $note = $this->query->show($note);

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation is handled upstream by UpdateNoteRequest, which also
     * implicitly authorises the operation via its authorize() method.
     *
     * After updating, an audit log entry is written against the
     * authenticated user.
     *
     * @param  UpdateNoteRequest $request Validated request containing updated
     * note data.
     * @param  Note $note Route-model-bound note instance to update.
     *
     * @return JsonResponse The updated note resource.
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
     * Authorises via the 'delete' policy before proceeding.
     *
     * The audit log entry is written before the deletion so that the
     * note instance is still fully accessible during logging.
     *
     * @param  Note $note Route-model-bound note instance to delete.
     *
     * @return JsonResponse Empty response with HTTP 204 No Content.
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
     * Restore the specified note from soft deletion.
     *
     * Looks up the note including trashed records, then authorises via
     * the 'restore' policy. Returns 404 if the note is not currently
     * soft-deleted, preventing accidental double-restores.
     *
     * @param  int|string $id The primary key of the soft-deleted note.
     *
     * @return JsonResponse The restored note resource.
     *
     * @throws ModelNotFoundException If no matching record exists.
     *
     * @throws HttpException If the note is not trashed (404).
     */
    public function restore(int $id): JsonResponse
    {
        $note = Note::withTrashed()->findOrFail($id);
        $this->authorize('restore', $note);

        if (! $note->trashed()) {
            abort(404);
        }

        $this->management->restore((int) $id);

        $user = auth()->user();

        $this->logger->noteRestored(
            $user,
            $user->id,
            $note
        );

        return response()->json($note);
    }
}
