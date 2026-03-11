<?php

namespace App\Services\Contacts;

use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;

class ContactCreatorService
{
    /**
     * Create a new contact from request data.
     *
     * @param StoreContactRequest $request
     *
     * @return Contact
     */
    public function create(StoreContactRequest $request): Contact
    {
        $user = $request->user();
        $data = $request->validated();

        $data['created_by'] = $user->id;
        $data['created_at'] = now();

        return Contact::create($data);
    }
}
