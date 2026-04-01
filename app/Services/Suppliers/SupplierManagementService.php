<?php

namespace App\Services\Suppliers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;

/**
 * Orchestrates supplier lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for supplier create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class SupplierManagementService
{
    /**
     * Service responsible for creating new supplier records.
     *
     * @var SupplierCreatorService
     */
    private SupplierCreatorService $creator;

    /**
     * Service responsible for updating existing supplier records.
     *
     * @var SupplierUpdaterService
     */
    private SupplierUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring supplier records.
     *
     * @var SupplierDestructorService
     */
    private SupplierDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  SupplierCreatorService $creator Handles supplier creation.
     * @param  SupplierUpdaterService $updater Handles supplier updates.
     * @param  SupplierDestructorService $destructor Handles supplier deletion
     * and restoration.
     */
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
     * @param  StoreSupplierRequest $request Validated request containing
     * supplier data.
     *
     * @return Supplier The newly created supplier.
     */
    public function store(StoreSupplierRequest $request): Supplier
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing supplier.
     *
     * @param UpdateSupplierRequest $request Validated request containing
     * updated supplier data.
     *
     * @param Supplier $supplier The supplier instance to update.
     *
     * @return Supplier The updated supplier.
     */
    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ): Supplier {
        return $this->updater->update($request, $supplier);
    }

    /**
     * Soft-delete a supplier.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Supplier $supplier The supplier to delete.
     *
     * @return void
     */
    public function destroy(Supplier $supplier): void
    {
        $this->destructor->destroy($supplier);
    }

    /**
     * Restore a soft-deleted supplier.
     *
     * @param int $id The primary key of the soft-deleted supplier.
     *
     * @return Supplier The restored supplier.
     */
    public function restore(int $id): Supplier
    {
        return $this->destructor->restore($id);
    }
}
