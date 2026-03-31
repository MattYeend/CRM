<?php

namespace App\Services\Deals;

use App\Models\Deal;

/**
 * Handles soft deletion and restoration of Deal records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by,
 * and restored_at columns are always populated.
 */
class DealDestructorService
{
    /**
     * Soft-delete a deal.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the deal.
     *
     * @param  Deal $deal The deal to soft-delete.
     *
     * @return void
     */
    public function destroy(Deal $deal): void
    {
        $userId = auth()->id();

        $deal->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $deal->delete();
    }

    /**
     * Restore a soft-deleted deal.
     *
     * Looks up the deal including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the deal. Returns the deal unchanged if it is not currently trashed.
     *
     * @param  int $id The primary key of the soft-deleted deal.
     *
     * @return Deal The restored deal instance.
     */
    public function restore(int $id): Deal
    {
        $userId = auth()->id();

        $deal = Deal::withTrashed()->findOrFail($id);

        if ($deal->trashed()) {
            $deal->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $deal->restore();
        }

        return $deal;
    }
}
