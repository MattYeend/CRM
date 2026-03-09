<?php

namespace App\Services;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;

class ContactManagementService
{
    private ContactCreatorService $creator;
    private ContactUpdaterService $updater;
    private ContactDestructorService $destructor;

    public function __construct(
        ContactCreatorService $creator,
        ContactUpdaterService $updater,
        ContactDestructorService $destructor
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new contact.
     *
     * @param StoreContactRequest $request
     *
     * @return Contact
     */
    public function store(StoreContactRequest $request): Contact
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing contact.
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
        return $this->updater->update($request, $contact);
    }

    /**
     * Delete a contact (soft delete).
     *
     * @param Contact $contact
     *
     * @return void
     */
    public function destroy(Contact $contact): void
    {
        $this->destructor->destroy($contact);
    }

    /**
     * Restore a soft-deleted contact.
     *
     * @param int $id
     *
     * @return Contact
     */
    public function restore(int $id): Contact
    {
        return $this->destructor->restore($id);
    }
}
