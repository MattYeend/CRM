<?php

namespace App\Services\Suppliers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

class SupplierManagementService
{
    private SupplierCreatorService $creator;
    private SupplierUpdaterService $updater;
    private SupplierDestructorService $destructor;

    public function __construct(
        SupplierCreatorService $creator,
        SupplierUpdaterService $updater,
        SupplierDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new supplier.
     *
     * @param StoreSupplierRequest $request
     *
     * @return Supplier
     */
    public function store(StoreSupplierRequest $request): Supplier
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing supplier.
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
        return $this->updater->update($request, $supplier);
    }

    /**
     * Delete a supplier (soft delete).
     *
     * @param Supplier $supplier
     *
     * @return void
     */
    public function destroy(Supplier $supplier): void
    {
        $this->destructor->destroy($supplier);
    }

    /**
     * Restore a soft-deleted supplier
     *
     * @param int $id
     *
     * @return Supplier
     */
    public function restore(int $id): Supplier
    {
        return $this->destructor->restore($id);
    }
}
