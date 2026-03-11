<?php

namespace App\Services\Contacts;

use App\Models\Contact;

class ContactDestructorService
{
    /**
     * Soft-delete a contact.
     *
     * @param Contact $contact
     *
     * @return void
     */
    public function destroy(Contact $contact): void
    {
        $contact->update([
            'deleted_by' => auth()->id(),
        ]);

        $contact->delete();
    }

    /**
     * Restore a trashed contact.
     *
     * @param int $id
     *
     * @return Contact
     */
    public function restore(int $id): Contact
    {
        $contact = Contact::withTrashed()->findOrFail($id);

        if ($contact->trashed()) {
            $contact->restore();
        }

        return $contact;
    }
}
