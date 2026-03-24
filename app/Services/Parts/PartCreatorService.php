<?php

namespace App\Services\Parts;

use App\Http\Requests\StorePartRequest;
use App\Models\Part;

class PartCreatorService
{
    /**
     * Create a new part from request data.
     *
     * @param StorePartRequest $request
     *
     * @return Part
     */
    public function create(StorePartRequest $request): Part
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Part::create($data);
    }
}
