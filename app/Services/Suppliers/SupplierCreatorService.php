<?php

namespace App\Services\Suppliers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;

/**
 * Handles the creation of new Supplier records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Supplier.
 */
class SupplierCreatorService
{
    /**
     * Create a new supplier from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreSupplierRequest $request Validated request containing
     * supplier data.
     *
     * @return Supplier The newly created supplier record.
     */
    public function create(StoreSupplierRequest $request): Supplier
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Supplier::create($data);
    }
}
