<?php

namespace App\Support;

/**
 * Registry for all bom entity types used across the application.
 *
 * Provides a central source of truth for available bom models,
 * their labels, and associated API endpoints.
 */
class BillOfMaterialsRegistory
{
    /**
     * Get all bom types with their metadata.
     *
     * Each entry contains:
     * - label: Human-readable name
     * - endpoint: API endpoint to fetch all records of that type
     *
     * @return array<string, array{label: string, endpoint: string}>
     */
    public static function all(): array
    {
        return [
            'part' => [
                'label' => 'Part',
                'endpoint' => '/api/parts/all',
            ],
            'product' => [
                'label' => 'Product',
                'endpoint' => '/api/product/all',
            ],
        ];
    }

    /**
     * Get all available bom type keys.
     *
     * Example: ['part', 'product']
     *
     * @return string
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get the API endpoint for a given bom type.
     *
     * @param string $type The bom type key (e.g. 'part')
     *
     * @return string|null The endpoint URL, or null if type does not exist
     */
    public static function endpoint(string $type): ?string
    {
        return self::all()[$type]['endpoint'] ?? null;
    }
}
