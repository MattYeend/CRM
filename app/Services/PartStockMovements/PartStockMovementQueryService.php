<?php

namespace App\Services\PartStockMovements;

use App\Models\PartStockMovement;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PartStockMovementQueryService
{
    private PartStockMovementSortingService $sorting;

    public function __construct(
        PartStockMovementSortingService $sorting,
    ) {
        $this->sorting = $sorting;
    }

    /**
     * Return paginated part stock movement, applying filters/sorting.
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

        $query = PartStockMovement::query();

        $this->sorting->applySorting($query, $request);

        return $query->paginate($perPage)->appends($request->query());
    }

    /**
     * Return a single part stock movement.
     *
     * @param PartStockMovement $partStockMovement
     *
     * @return PartStockMovement
     */
    public function show(
        PartStockMovement $partStockMovement
    ): PartStockMovement {
        return $partStockMovement;
    }
}
