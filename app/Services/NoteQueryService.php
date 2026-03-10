<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class NoteQueryService
{
    private NoteSortingService $sorting;
    private TrashFilterService $trashFilter;
    public function __construct(
        NoteSortingService $sorting,
        TrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return paginated note, applying filters/sorting.
     *
     * @param Request $request
     *
     * @return LengthAwarePaginator
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
     * Return a single note.
     *
     * @param Note $note
     *
     * @return Note
     */
    public function show(Note $note): Note
    {
        return $note->load('user', 'notable');
    }
}
