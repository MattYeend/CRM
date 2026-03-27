<?php

namespace App\Exceptions;

use Exception;

class InsufficientStock extends Exception
{
    /**
     * Create a new InsufficientStock instance.
     *
     * @param int $currentQuantity
     *
     * @param int $requestedQuantity
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
     * Get the current quantity.
     *
     * @return int
     */
    public function getCurrentQuantity(): int
    {
        return $this->currentQuantity;
    }

    /**
     * Get the requested quantity.
     *
     * @return int
     */
    public function getRequestedQuantity(): int
    {
        return $this->requestedQuantity;
    }
}
