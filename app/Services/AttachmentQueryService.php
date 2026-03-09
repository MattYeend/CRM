<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttachmentQueryService
{
    /**
     * Return paginated attachment with roles, applying filters/sorting.
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

        $query = Attachment::query();

        $this->applyTrashFilters($query, $request);
        $this->applySorting($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single attachment with roles loaded.
     *
     * @param Attachment $attachment
     *
     * @return Attachment
     */
    public function show(Attachment $attachment): Attachment
    {
        return $attachment->load('uploader');
    }

    /**
     * Apply trash filters to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applyTrashFilters($query, Request $request): void
    {
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();

            return;
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $allowedSorts = [
            'id',
            'filename',
            'created_at',
            'updated_at',
        ];

        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
