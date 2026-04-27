<?php

namespace App\Services\BillOfMaterials;

use App\Models\BillOfMaterial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Handles read queries for Bill of Materials (BOM) records.
 *
 * Delegates sorting and trash filtering to dedicated sub-services and
 * returns paginated BOM results with the appropriate relationships loaded.
 */
class BillOfMaterialQueryService
{
    /**
     * Service responsible for applying sort order to BOM queries.
     *
     * @var BillOfMaterialSortingService
     */
    private BillOfMaterialSortingService $sorting;

    /**
     * Service responsible for applying trash visibility filters to BOM queries.
     *
     * @var BillOfMaterialTrashFilterService
     */
    private BillOfMaterialTrashFilterService $trashFilter;

    /**
     * Inject the required services into the query service.
     *
     * @param  BillOfMaterialSortingService $sorting Handles sort order
     * application.
     * @param  BillOfMaterialTrashFilterService $trashFilter Handles trash
     * visibility filtering.
     */
    public function __construct(
        BillOfMaterialSortingService $sorting,
        BillOfMaterialTrashFilterService $trashFilter,
    ) {
        $this->sorting = $sorting;
        $this->trashFilter = $trashFilter;
    }

    /**
     * Return a paginated list of BOM entries for a given manufacturable entity.
     *
     * @param  Model $manufacturable
     * @param  Request $request
     *
     * @return array
     */
    public function list(Model $manufacturable, Request $request): array
    {
        $query = $manufacturable->billOfMaterials()
            ->with('childPart:id,sku,name,quantity,unit_of_measure')
            ->getQuery();

        $this->sorting->applySorting($query, $request);
        $this->trashFilter->applyTrashFilters($query, $request);

        $paginator = $this->paginate($query, $request);

        return array_merge(
            $paginator,
            ['permissions' => $this->getPermissions()]
        );
    }


    /**
     * Paginate and transform the BOM query.
     *
     * @param  Builder $query
     * @param  Request $request
     *
     * @return array
     */
    private function paginate(Builder $query, Request $request): array
    {
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        return $query->paginate($perPage)
            ->appends($request->query())
            ->through(
                fn (BillOfMaterial $bom): array => $this->formatBOM($bom)
            )
            ->toArray();
    }

    /**
     * Get top-level permission flags for the current user.
     *
     * @return array
     */
    private function getPermissions(): array
    {
        return [
            'create' => Gate::allows('create', BillOfMaterial::class),
            'viewAny' => Gate::allows('viewAny', BillOfMaterial::class),
        ];
    }

    /**
     * Format a Bill Of Material into a structured array.
     *
     * Includes core attributes, related child part data, creator,
     * and authorisation permissions for the current user.
     *
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return array
     */
    private function formatBOM(BillOfMaterial $billOfMaterial): array
    {
        return array_merge(
            $this->baseData($billOfMaterial),
            $this->relationshipData($billOfMaterial),
            $this->permissionData($billOfMaterial),
        );
    }

    /**
     * Extract core BOM attributes.
     *
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return array
     */
    private function baseData(BillOfMaterial $billOfMaterial): array
    {
        return [
            'id' => $billOfMaterial->id,
            'manufacturable_type' => $billOfMaterial->manufacturable_type,
            'manufacturable_id' => $billOfMaterial->manufacturable_id,
            'child_part_id' => $billOfMaterial->child_part_id,
            'quantity' => $billOfMaterial->quantity,
            'unit_of_measure' => $billOfMaterial->unit_of_measure,
            'scrap_percentage' => $billOfMaterial->scrap_percentage,
            'notes' => $billOfMaterial->notes,
        ];
    }

    /**
     * Extract related model data for the BOM entry.
     *
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return array
     */
    private function relationshipData(BillOfMaterial $billOfMaterial): array
    {
        return [
            'manufacturable' => $billOfMaterial->manufacturable,
            'child_part' => $billOfMaterial->childPart,
            'creator' => $billOfMaterial->creator,
        ];
    }

    /**
     * Determine authorisation permissions for the BOM entry.
     *
     * @param  BillOfMaterial $billOfMaterial
     *
     * @return array
     */
    private function permissionData(BillOfMaterial $billOfMaterial): array
    {
        return [
            'permissions' => [
                'view' => Gate::allows('view', $billOfMaterial),
                'update' => Gate::allows('update', $billOfMaterial),
                'delete' => Gate::allows('delete', $billOfMaterial),
            ],
        ];
    }
}
