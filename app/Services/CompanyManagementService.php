<?php

namespace App\Services;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;

class CompanyManagementService
{
    private CompanyCreatorService $creator;
    private CompanyUpdaterService $updater;
    private CompanyDestructorService $destructor;

    public function __construct(
        CompanyCreatorService $creator,
        CompanyUpdaterService $updater,
        CompanyDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new company.
     *
     * @param StoreCompanyRequest $request
     *
     * @return Company
     */
    public function store(StoreCompanyRequest $request): Company
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing company.
     *
     * @param UpdateCompanyRequest $request
     *
     * @param Company $company
     *
     * @return Company
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): Company {
        return $this->updater->update($request, $company);
    }

    /**
     * Delete a company (soft delete).
     *
     * @param Company $company
     *
     * @return void
     */
    public function destroy(Company $company): void
    {
        $this->destructor->destroy($company);
    }

    /**
     * Restore a soft-deleted company.
     *
     * @param int $id
     *
     * @return Company
     */
    public function restore(int $id): Company
    {
        return $this->destructor->restore($id);
    }
}
