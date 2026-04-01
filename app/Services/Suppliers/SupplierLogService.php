<?php

namespace App\Services\Suppliers;

use App\Models\Log;
use App\Models\Supplier;
use App\Models\User;

/**
 * Handles audit logging for Supplier lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific supplier action, combining base supplier data with
 * action-specific timestamp and actor fields.
 */
class SupplierLogService
{
    /**
     * Log a supplier creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Supplier $supplier The supplier that was created.
     *
     * @return array The structured data written to the log entry.
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
     * Log a supplier update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Supplier $supplier The supplier that was updated.
     *
     * @return array The structured data written to the log entry.
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
     * Log a supplier deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Supplier $supplier The supplier that was deleted.
     *
     * @return array The structured data written to the log entry.
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
     * Log a supplier restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Supplier $supplier The supplier that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all supplier log entries.
     *
     * Merges all data sub-groups into a single array covering identity,
     * address, pricing, contact, flags, and metadata.
     *
     * @param  Supplier $supplier The supplier being logged.
     *
     * @return array The complete base data array.
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
     * Build the primary key data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the identity and contact information data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the postal address data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the pricing, payment, and tax data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the primary contact person data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the boolean flag and notes data for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
     * Build the metadata for the log entry.
     *
     * @param  Supplier $supplier The supplier being logged.
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
