<?php

namespace App\Services\Suppliers;

use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

/**
 * Handles updates to Supplier records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the supplier.
 */
class SupplierUpdaterService
{
    /**
     * Update an existing supplier.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the supplier, and returns
     * a fresh instance.
     *
     * @param  UpdateSupplierRequest $request The request containing
     * validated supplier data.
     * @param  Supplier $supplier The supplier to update.
     *
     * @return Supplier The updated supplier instance.
     */
    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ): Supplier {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $supplier->update($data);

        return $supplier->fresh();
    }
}
