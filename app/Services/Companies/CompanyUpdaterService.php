<?php

namespace App\Services\Companies;

use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;

/**
 * Handles updates to Company records.
 *
 * Validates incoming data, assigns audit fields, and persists updates
 * to the company record.
 */
class CompanyUpdaterService
{
    /**
     * Update an existing company.
     *
     * Applies validated request data, records the authenticated user
     * and timestamp in audit fields, and returns a fresh instance of
     * the updated company.
     *
     * @param  UpdateCompanyRequest $request The request containing
     * validated update data.
     * @param  Company $company The company to update.
     *
     * @return Company The updated company instance.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): Company {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;

        $company->update($data);

        return $company->fresh();
    }
}
