<?php

namespace App\Services\Notes;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single note with related data loaded.
     *
     * @param  Note $note The route-model-bound note
     * instance.
     *
     * @return Note The note with relationships loaded.
     */
    public function show(Note $note): Note
    {
        return $note->load('user', 'notable');
    }
}
