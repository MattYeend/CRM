<?php

namespace App\Services;

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
        $deal->update([
            'deleted_by' => auth()->id(),
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
        $deal = Deal::withTrashed()->findOrFail($id);

        if ($deal->trashed()) {
            $deal->restore();
        }

        return $deal;
    }
}
