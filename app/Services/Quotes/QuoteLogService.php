<?php

namespace App\Services\Quotes;

use App\Models\Log;
use App\Models\Quote;
use App\Models\User;

class QuoteLogService
{
    /**
     * Log the creation of a Quote.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Quote $quote The quote was created.
     *
     * @return Log The created log entry.
     */
    public function quoteCreated(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'created_at' => $quote->created_at,
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_QUOTE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the update of a Quote.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Quote $quote The quote was updated.
     *
     * @return Log The created log entry.
     */
    public function quoteUpdated(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'updated_at' => $quote->updated_at,
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_QUOTE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the deletion of a Quote.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Quote $quote The quote was deleted.
     *
     * @return Log The created log entry.
     */
    public function quoteDeleted(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'deleted_at' => $quote->deleted_at,
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_QUOTE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log the restoration of a Quote.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Quote $quote The quote was restored.
     *
     * @return Log The created log entry.
     */
    public function quoteRestored(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_QUOTE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Prepare base data for Quote logging.
     *
     * @param Quote $quote The quote to extract data from.
     *
     * @return array The base quote data.
     */
    protected function baseQuoteData(Quote $quote): array
    {
        return [
            'id' => $quote->id,
            'deal_id' => $quote->deal_id,
            'currency' => $quote->currency,
            'subtotal' => $quote->subtotal,
            'tax' => $quote->tax,
            'total' => $quote->total,
            'sent_at' => $quote->sent_at,
            'accepted_at' => $quote->accepted_at,
            'meta' => $quote->meta,
        ];
    }
}
