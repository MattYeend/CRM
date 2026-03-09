<?php

namespace App\Services;

use App\Models\Lead;

class LeadDestructorService
{
    /**
     * Soft-delete a lead.
     *
     * @param Lead $lead
     *
     * @return void
     */
    public function destroy(Lead $lead): void
    {
        $lead->update([
            'deleted_by' => auth()->id(),
        ]);

        $lead->delete();
    }

    /**
     * Restore a trashed lead.
     *
     * @param int $id
     *
     * @return Lead
     */
    public function restore(int $id): Lead
    {
        $lead = Lead::withTrashed()->findOrFail($id);

        if ($lead->trashed()) {
            $lead->restore();
        }

        return $lead;
    }
}
