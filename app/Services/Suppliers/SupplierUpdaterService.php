<?php

namespace App\Services\Suppliers;

use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierUpdaterService
{
    /**
     * Update the supplier using request data.
     *
     * @param UpdateSupplierRequest $request
     *
     * @param Supplier $supplier
     *
     * @return Supplier
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
