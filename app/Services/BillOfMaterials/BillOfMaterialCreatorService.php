<?php

namespace App\Services\BillOfMaterials;

use App\Http\Requests\StoreBillOfMaterialRequest;
use App\Models\BillOfMaterial;
use App\Models\Part;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Handles creation of Bill of Materials (BOM) entries.
 *
 * Validates business rules before creating a new BOM entry, ensuring
 * that only manufactured parts can have child parts and that a part
 * cannot contain itself.
 */
class BillOfMaterialCreatorService
{
    /**
     * Create a new BOM entry.
     *
     * Validates the request and parent part, then creates a new
     * BillOfMaterial record linking the parent and child parts.
     *
     * @param  StoreBillOfMaterialRequest $request The validated request
     * data for creating the BOM.
     * @param  Model $manufacturable The parent part to which the child part
     * will be added.
     *
     * @return BillOfMaterial The newly created BOM entry with loaded relations.
     *
     * @throws HttpResponseException If the parent part is not manufactured
     *         or if the part attempts to contain itself.
     */
    public function create(
        StoreBillOfMaterialRequest $request,
        Model $manufacturable
    ): BillOfMaterial {
        if (
            ! $manufacturable->hasBom()
        ) {
            abort(422, 'This item cannot have a BOM.');
        }

        if (
            $manufacturable instanceof Part &&
            $request->child_part_id === $manufacturable->id
        ) {
            abort(422, 'An item cannot contain itself.');
        }

        return BillOfMaterial::create([
            ...$request->validated(),
            'manufacturable_type' => $manufacturable->getMorphClass(),
            'manufacturable_id' => $manufacturable->id,
        ])->load('childPart', 'manufacturable');
    }
}
