<?php

namespace App\Services\Parts;

use App\Http\Requests\UpdatePartRequest;
use App\Models\Part;

class PartUpdaterService
{
    /**
     * Update the part using request data.
     *
     * @param UpdatePartRequest $request
     *
     * @param Part $part
     *
     * @return Part
     */
    public function update(
        UpdatePartRequest $request,
        Part $part
    ): Part {
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $part->update($data);

        return $part->fresh();
    }
}
