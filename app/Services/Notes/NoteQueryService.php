<?php

namespace App\Services\Notes;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Note records.
 *
 * Delegates searching, sorting, and trash filtering to dedicated
 * sub-services and returns paginated or single note results with
 * the appropriate relationships loaded.
 */
class NoteQueryService
{
    /**
     * Service responsible for applying sort order.
     *
     * @var NoteSortingService
     */
    private NoteSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters.
     *
     * @var NoteTrashFilterService
     */
    private NoteTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  NoteSortingService $sorting Handles sort order.
     * @param  NoteTrashFilterService $trashFilter Handles
     * trash filtering.
     */
    public function __construct(
        NoteSortingService $sorting,
        NoteTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of notes with search, sorting,
     * and trash filters applied.
     *
     * The per_page value is clamped between 1 and 100. All active query
     * string parameters are appended to the paginator links.
     *
     * @param  Request $request Incoming HTTP request; may carry search,
     * sort, filter, and pagination params.
     *
     * @return LengthAwarePaginator Paginated notes item results.
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $perPage = max(
            1,
            min((int) $request->query('per_page', 10), 100)
        );

        $query = Note::with(
            'user',
            'notable',
        );

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $query->paginate($perPage)->appends($request->query());

        return $this->transformPaginator($paginator);
    }

    /**
     * Return a single note with related data loaded.
     *
     * @param  Note $note The route-model-bound note
     * instance.
     *
     * @return array
     */
    public function show(Note $note): array
    {
        $note->load(
            'user',
            'notable',
        );

        return $this->formatNote($note);
    }

    /**
     * Apply transformation and append permissions to the paginator.
     *
     * Each note item is formatted into a structured array and
     * top-level permissions are appended to the paginator response.
     *
     * @param  LengthAwarePaginator $paginator The paginator instance
     * containing Note models.
     *
     * @return LengthAwarePaginator The transformed paginator instance.
     */
    private function transformPaginator(
        LengthAwarePaginator $paginator
    ): LengthAwarePaginator {
        $paginator->through(
            fn (Note $note) => $this->formatNote($note)
        );

        $paginator->appends([
            'permissions' => [
                'create' => Gate::allows('create', Note::class),
                'viewAny' => Gate::allows('viewAny', Note::class),
            ],
        ]);

        return $paginator;
    }

    /**
     * Format a note into a structured array.
     *
     * Includes core attributes, related user data, derived notable name,
     * and authorisation permissions for the current user.
     *
     * @param  Note  $note
     *
     * @return array
     */
    private function formatNote(Note $note): array
    {
        return [
            'id' => $note->id,
            'body' => $note->type,
            'notable_type' => $note->notable_type,
            'notable_id' => $note->notable_id,
            'notable_name' => $this->notableName($note),
            'notable' => $note->notable,
            'user' => $note->user,
            'creator' => $note->creator,
            'permissions' => [
                'view' => Gate::allows('view', $note),
                'update' => Gate::allows('update', $note),
                'delete' => Gate::allows('delete', $note),
            ],
        ];
    }

    /**
     * Resolve the notable name for a note.
     *
     * Attempts to derive a displayable name from the related notable
     * using common attributes such as `name` or `title`.
     *
     * @param  Note $note
     *
     * @return string|null
     */
    private function notableName(Note $note): ?string
    {
        if ($note->notable) {
            return $note->notable->name
                ?? $note->notable->title
                ?? null;
        }

        return null;
    }
}
