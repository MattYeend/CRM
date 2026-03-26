<?php

namespace App\Services\Suppliers;

use App\Models\Log;
use App\Models\Supplier;
use App\Models\User;

class SupplierLogService
{
    /**
     * Log the creation of a Supplier.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Supplier $supplier The supplier was created.
     *
     * @return Log The created log entry.
     */
    public function supplierCreated(
        User $user,
        int $userId,
        Supplier $supplier
    ): array {
        $data = $this->baseSupplierData($supplier) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_SUPPLIER_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Supplier.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Supplier $supplier The supplier was updated.
     *
     * @return Log The created log entry.
     */
    public function supplierUpdated(
        User $user,
        int $userId,
        Supplier $supplier
    ): array {
        $data = $this->baseSupplierData($supplier) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_SUPPLIER_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Supplier.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Supplier $supplier The supplier was deleted.
     *
     * @return Log The created log entry.
     */
    public function supplierDeleted(
        User $user,
        int $userId,
        Supplier $supplier
    ): array {
        $data = $this->baseSupplierData($supplier) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_SUPPLIER_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Supplier.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Supplier $supplier The supplier was restored.
     *
     * @return Log The created log entry.
     */
    public function supplierRestored(
        User $user,
        int $userId,
        Supplier $supplier
    ): array {
        $data = $this->baseSupplierData($supplier) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_SUPPLIER_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare the base data for logging a Supplier.
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array The base data array.
     */
    protected function baseSupplierData(Supplier $supplier): array
    {
        return array_merge(
            $this->baseData($supplier),
            $this->identifyData($supplier),
            $this->addressData($supplier),
            $this->pricingData($supplier),
            $this->contactData($supplier),
            $this->flagData($supplier),
            $this->metaData($supplier),
        );
    }

    /**
     * Base data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function baseData(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
        ];
    }

    /**
     * Identify data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function identifyData(Supplier $supplier): array
    {
        return [
            'name' => $supplier->name,
            'code' => $supplier->code,
            'email' => $supplier->email,
            'phone' => $supplier->phone,
            'website' => $supplier->website,
        ];
    }

    /**
     * Address data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function addressData(Supplier $supplier): array
    {
        return [
            'address_line_1' => $supplier->address_line_1,
            'address_line_2' => $supplier->address_line_2,
            'city' => $supplier->city,
            'county' => $supplier->county,
            'postcode' => $supplier->postcode,
            'country' => $supplier->country,
        ];
    }

    /**
     * Pricing data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function pricingData(Supplier $supplier): array
    {
        return [
            'currency' => $supplier->currency,
            'payment_terms' => $supplier->payment_terms,
            'tax_number' => $supplier->tax_number,
        ];
    }

    /**
     * Contact data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function contactData(Supplier $supplier): array
    {
        return [
            'contact_name' => $supplier->contact_name,
            'contact_email' => $supplier->contact_email,
            'contact_phone' => $supplier->contact_phone,
        ];
    }

    /**
     * Flag data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function flagData(Supplier $supplier): array
    {
        return [
            'is_active' => $supplier->is_active,
            'notes' => $supplier->notes,
            'is_test' => $supplier->is_test,
        ];
    }

    /**
     * Meta data
     *
     * @param Supplier $supplier The supplier being logged.
     *
     * @return array
     */
    private function metaData(Supplier $supplier): array
    {
        return [
            'meta' => $supplier->meta,
        ];
    }
}
