<?php

namespace App\Services\Leads;

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
        $userId = auth()->id();

        $lead->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
