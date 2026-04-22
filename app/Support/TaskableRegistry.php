<?php

namespace App\Support;

/**
 * Registry of entities that can have tasks assigned to them.
 *
 * Defines which entity types support task assignment,
 * along with labels and API endpoints.
 */
class TaskableRegistry
{
    /**
     * Get all taskable entity types with metadata.
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
            'company' => [
                'label' => 'Company',
                'endpoint' => '/api/companies/all',
            ],
            'deal' => [
                'label' => 'Deal',
                'endpoint' => '/api/deals/all',
            ],
            'user' => [
                'label' => 'User',
                'endpoint' => '/api/users/all',
            ],
        ];
    }

    /**
     * Get all taskable registry keys.
     *
     * Example: ['company', 'deal', 'user']
     *
     * @return string
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get the API endpoint for a given taskable registry type.
     *
     * @param string $type The registry type key
     *
     * @return string|null The endpoint URL or null if not found
     */
    public static function endpoint(string $type): ?string
    {
        return self::all()[$type]['endpoint'] ?? null;
    }
}
