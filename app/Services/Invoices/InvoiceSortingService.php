<?php

namespace App\Services\Invoices;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class InvoiceSortingService
{
    /**
     * Constants for allowed sorts
     */
    private const ALLOWED_SORTS = [
        'id',
        'number',
        'company_id',
        'contact_id',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax',
        'total',
        'currency',
        'created_at',
        'updated_at',
    ];

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     *
     * @param Request $request
     *
     * @return void
     */
    public function applySorting($query, Request $request): void
    {
        $sortBy = $request->query('sort_by', 'id');

        if (! in_array($sortBy, self::ALLOWED_SORTS, true)) {
            $sortBy = 'id';
        }

        $sortDir = $request->query('sort_dir', 'desc') === 'asc'
            ? 'asc'
            : 'desc';

        $query->orderBy($sortBy, $sortDir);
    }
}
