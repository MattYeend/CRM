<?php

namespace App\Services\Quotes;

use App\Models\Quote;

/**
 * Handles soft deletion and restoration of Quote records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class QuoteDestructorService
{
    /**
     * Soft-delete a quote.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the quote.
     *
     * @param  Quote $quote The quote instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Quote $quote): void
    {
        $userId = auth()->id();

        $quote->update([
            'deleted_by' => $userId,
        ]);

        $quote->delete();
    }

    /**
     * Restore a soft-deleted quote.
     *
     * Looks up the quote including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the quote. Returns the quote unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted quote.
     *
     * @return Quote The restored quote instance.
     */
    public function restore(int $id): Quote
    {
        $userId = auth()->id();

        $quote = Quote::withTrashed()->findOrFail($id);

        if ($quote->trashed()) {
            $quote->update([
                'updated_by' => $userId,
            ]);
            $quote->restore();
        }

        return $quote;
    }
}
