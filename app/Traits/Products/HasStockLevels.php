<?php

namespace App\Traits\Products;

/**
 * Provides stock-related attributes and query scopes.
 *
 * This trait encapsulates common inventory logic such as determining
 * whether a model is low on stock or completely out of stock, as well
 * as query scopes for filtering based on stock levels.
 *
 * Intended for use on models that have:
 * - quantity
 * - reorder_point (nullable)
 */
trait HasStockLevels
{
    /**
     * Determine whether the product is low on stock.
     *
     * Returns true when a reorder point is set and the current quantity
     * is at or below it. Products without a reorder point configured are
     * never considered low stock.
     *
     * @return bool
     */
    public function getIsLowStockAttribute(): bool
    {
        return $this->reorder_point !== null
            && $this->quantity <= $this->reorder_point;
    }

    /**
     * Determine whether the product is out of stock.
     *
     * Returns true when the current quantity is exactly zero.
     *
     * @return bool
     */
    public function getIsOutOfStockAttribute(): bool
    {
        return $this->quantity === 0;
    }

    /**
     * Scope a query to products that are out of stock.
     *
     * Filters to products with a quantity of zero regardless of status,
     * allowing identification of stock gaps across all product states.
     *
     * @param  Builder<Product> $query The query builder instance.
     *
     * @return Builder<Product> The modified query builder instance.
     */
    public function scopeLowStock($query)
    {
        return $query->whereNotNull('reorder_point')
            ->whereColumn('quantity', '<=', 'reorder_point');
    }

    /**
     * Scope a query to only include models that are out of stock.
     *
     * Filters records where quantity equals zero.
     *
     * @param  Builder $query
     *
     * @return Builder
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }
}
