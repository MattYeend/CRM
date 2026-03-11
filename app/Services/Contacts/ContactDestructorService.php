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
        $userId = auth()->id();

        $contact->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
        $userId = auth()->id();

        $contact = Contact::withTrashed()->findOrFail($id);

        if ($contact->trashed()) {
            $contact->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $contact->restore();
        }

        return $contact;
    }
}
