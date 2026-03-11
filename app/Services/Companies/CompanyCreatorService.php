<?php

namespace App\Services\Companies;

use App\Http\Requests\StoreCompanyRequest;
use App\Models\Company;

class CompanyCreatorService
{
    /**
     * Create a new company from request data.
     *
     * @param StoreCompanyRequest $request
     *
     * @return Company
     */
    public function create(StoreCompanyRequest $request): Company
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Company::create($data);
    }
}
