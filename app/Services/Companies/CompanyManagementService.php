<?php

namespace App\Services\Companies;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;

/**
 * Central service for managing Company records.
 *
 * Delegates creation, update, deletion, and restoration operations to
 * the respective creator, updater, and destructor services, providing
 * a unified interface for company management.
 */
class CompanyManagementService
{
    private CompanyCreatorService $creator;
    private CompanyUpdaterService $updater;
    private CompanyDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  CompanyCreatorService $creator Handles company creation.
     * @param  CompanyUpdaterService $updater Handles company updates.
     * @param  CompanyDestructorService $destructor Handles deletion and
     * restoration.
     */
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
     * Delegates to the creator service to validate and store the company.
     *
     * @param  StoreCompanyRequest $request The request containing company data.
     *
     * @return Company The newly created company instance.
     */
    public function store(StoreCompanyRequest $request): Company
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing company.
     *
     * Delegates to the updater service to modify the company data.
     *
     * @param  UpdateCompanyRequest $request The request containing
     * updated company data.
     * @param  Company $company The company to update.
     *
     * @return Company The updated company instance.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): Company {
        return $this->updater->update($request, $company);
    }

    /**
     * Soft-delete a company.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Company $company The company to delete.
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
     * Delegates to the destructor service to restore the company.
     *
     * @param  int $id The primary key of the soft-deleted company.
     *
     * @return Company The restored company instance.
     */
    public function restore(int $id): Company
    {
        return $this->destructor->restore($id);
    }
}
