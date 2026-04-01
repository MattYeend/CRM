<?php

namespace App\Services\Quotes;

use App\Models\Log;
use App\Models\Quote;
use App\Models\User;

/**
 * Handles audit logging for Quote lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific quote action, combining base quote data with
 * action-specific timestamp and actor fields.
 */
class QuoteLogService
{
    /**
     * Log a quote creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Quote $quote The quote that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function quoteCreated(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'created_at' => now(),
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
     * Log a quote update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Quote $quote The quote that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function quoteUpdated(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'updated_at' => now(),
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
     * Log a quote deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Quote $quote The quote that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function quoteDeleted(
        User $user,
        int $userId,
        Quote $quote
    ): array {
        $data = $this->baseQuoteData($quote) + [
            'deleted_at' => now(),
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
     * Log a quote restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Quote $quote The quote that was restored.
     *
     * @return array The structured data written to the log entry.
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
     * Build the base data array shared across all quote log entries.
     *
     * @param  Quote $quote The quote being logged.
     *
     * @return array The base fields extracted from the quote.
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
