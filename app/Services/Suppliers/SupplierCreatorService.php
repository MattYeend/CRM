<?php

namespace App\Services\Suppliers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;

class SupplierCreatorService
{
    /**
     * Create a new supplier from request data.
     *
     * @param StoreSupplierRequest $request
     *
     * @return Supplier
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
