<?php

namespace App\Services\Companies;

use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;

class CompanyUpdaterService
{
    /**
     * Update the contact using request data.
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
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $company->update($data);

        return $company->fresh();
    }
}
