<?php

namespace App\Services\Companies;

use App\Models\Company;

/**
 * Handles soft deletion and restoration of Company records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class CompanyDestructorService
{
    /**
     * Soft-delete a company.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the company.
     *
     * @param  Company $company The company to soft-delete.
     *
     * @return void
     */
    public function destroy(Company $company): void
    {
        $userId = auth()->id();

        $company->update([
            'deleted_by' => $userId,
        ]);

        $company->delete();
    }

    /**
     * Restore a soft-deleted company.
     *
     * Looks up the company including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the company. Returns the company unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted company.
     *
     * @return Company The restored company instance.
     */
    public function restore(int $id): Company
    {
        $userId = auth()->id();

        $company = Company::withTrashed()->findOrFail($id);

        if ($company->trashed()) {
            $company->update([
                'restored_by' => $userId,
            ]);
            $company->restore();
        }

        return $company;
    }
}
