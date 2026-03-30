<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when a stock operation cannot be fulfilled
 * due to insufficient available quantity.
 *
 * Carries both the current stock level and the requested amount,
 * allowing the caller to make informed decisions or surface a
 * meaningful error message to the user.
 *
 * Example usage:
 *   throw new InsufficientStock(currentQuantity: 3, requestedQuantity: 10);
 *
 * Example message produced:
 *   "Insufficient stock. Current: 3, requested: 10."
 */
class InsufficientStock extends Exception
{
    /**
     * Create a new InsufficientStock exception instance.
     *
     * Automatically builds a descriptive message from the two quantities
     * and passes it up to the parent Exception constructor, so the message
     * is always consistent and does not need to be set manually by the caller.
     *
     * @param int $currentQuantity The quantity currently available in stock.
     * @param int $requestedQuantity The quantity that was requested but could
     * not be fulfilled.
     */
    public function __construct(
        private readonly int $currentQuantity,
        private readonly int $requestedQuantity,
    ) {
        parent::__construct(
            sprintf(
                'Insufficient stock. Current: %d, requested: %d.',
                $currentQuantity,
                $requestedQuantity,
            )
        );
    }

    /**
     * Get the quantity currently available in stock at the time the exception
     * was thrown.
     *
     * @return int The available stock quantity.
     */
    public function getCurrentQuantity(): int
    {
        return $this->currentQuantity;
    }

    /**
     * Get the quantity that was requested but could not be fulfilled.
     *
     * @return int The requested quantity.
     */
    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }
}
