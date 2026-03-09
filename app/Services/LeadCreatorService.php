<?php

namespace App\Services;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;

class LeadCreatorService
{
    /**
     * Create a new lead from request data.
     *
     * @param StoreLeadRequest $request
     *
     * @return Lead
     */
    public function create(StoreLeadRequest $request): Lead
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;

        return Lead::create($data);
    }
}
