<?php

namespace App\Services;

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
        $company->update([
            'deleted_by' => auth()->id(),
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
        $company = Company::withTrashed()->findOrFail($id);

        if ($company->trashed()) {
            $company->restore();
        }

        return $company;
    }
}
