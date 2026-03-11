<?php

namespace App\Services\Companies;

use App\Models\Company;

class CompanyDestructorService
{
    /**
     * Soft-delete a company.
     *
     * @param Company $company
     *
     * @return void
     */
    public function destroy(Company $company): void
    {
        $userId = auth()->id();

        $company->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $company->delete();
    }

    /**
     * Restore a trashed company.
     *
     * @param int $id
     *
     * @return Company
     */
    public function restore(int $id): Company
    {
        $userId = auth()->id();

        $company = Company::withTrashed()->findOrFail($id);

        if ($company->trashed()) {
            $company->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $company->restore();
        }

        return $company;
    }
}
