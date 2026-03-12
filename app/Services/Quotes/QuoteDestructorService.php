<?php

namespace App\Services\Quotes;

use App\Models\Quote;

class QuoteDestructorService
{
    /**
     * Soft-delete a quote.
     *
     * @param Quote $quote
     *
     * @return void
     */
    public function destroy(Quote $quote): void
    {
        $userId = auth()->id();

        $quote->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $quote->delete();
    }

    /**
     * Restore a trashed quote.
     *
     * @param int $id
     *
     * @return Quote
     */
    public function restore(int $id): Quote
    {
        $userId = auth()->id();

        $quote = Quote::withTrashed()->findOrFail($id);

        if ($quote->trashed()) {
            $quote->update([
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
            $quote->restore();
        }

        return $quote;
    }
}
