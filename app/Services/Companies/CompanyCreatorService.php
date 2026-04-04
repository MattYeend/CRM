<?php

namespace App\Services\Companies;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;

/**
 * Handles creation of Company records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * a new company record.
 */
class CompanyCreatorService
{
    /**
     * Create a new company.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, and creates the company record.
     *
     * @param  StoreCompanyRequest $request The request containing validated
     * company data.
     *
     * @return Company The newly created company instance.
     */
    public function create(StoreCompanyRequest $request): Company
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Company::create($data);
    }
}
