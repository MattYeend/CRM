<?php

namespace App\Services\Deals;

use App\Models\Deal;

class DealDestructorService
{
    /**
     * Soft-delete a deal.
     *
     * @param Deal $deal
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
     * Restore a trashed deal.
     *
     * @param int $id
     *
     * @return Deal
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
