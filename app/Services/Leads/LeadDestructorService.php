<?php

namespace App\Services\Leads;

use App\Models\Lead;

/**
 * Handles soft deletion and restoration of Lead records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class LeadDestructorService
{
    /**
     * Soft-delete a lead.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the lead.
     *
     * @param  Lead $lead The lead instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Lead $lead): void
    {
        $userId = auth()->id();

        $lead->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $lead->delete();
    }

    /**
     * Restore a soft-deleted lead.
     *
     * Looks up the lead including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the lead. Returns the lead unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted lead.
     *
     * @return Lead The restored lead instance.
     */
    public function restore(int $id): Lead
    {
        $userId = auth()->id();

        $lead = Lead::withTrashed()->findOrFail($id);

        if ($lead->trashed()) {
            $lead->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $lead->restore();
        }

        return $lead;
    }
}
