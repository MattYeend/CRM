<?php

namespace App\Services;

use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;

class ContactUpdaterService
{
    /**
     * Update the contact using request data.
     *
     * @param UpdateContactRequest $request
     *
     * @param Contact $contact
     *
     * @return Contact
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): Contact {
        $data = $request->validated();

        $data['updated_by'] = $request->user()->id;

        $contact->update($data);

        return $contact->fresh();
    }
}
