<?php

namespace App\Services\Companies;

use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;

class CompanyUpdaterService
{
    /**
     * Update the company using request data.
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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $company->update($data);

        return $company->fresh();
    }
}
