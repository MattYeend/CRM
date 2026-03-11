<?php

namespace App\Services\Contacts;

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
        $user = $request->user();

        $data = $request->validated();

        $data['updated_by'] = $user->id;
        $data['updated_at'] = now();

        $contact->update($data);

        return $contact->fresh();
    }
}
