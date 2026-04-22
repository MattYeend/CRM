<?php

namespace App\Support;

/**
 * Registry of entities that users can interact with or be associated to.
 *
 * Provides a central definition of available user-related attachable types,
 * including labels and API endpoints.
 */
class UserRegistry
{
    /**
     * Get all user-related entity types with metadata.
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
            'task' => [
                'label' => 'Task',
                'endpoint' => '/api/tasks/all',
            ],
            'user' => [
                'label' => 'User',
                'endpoint' => '/api/users/all',
            ],
        ];
    }

    /**
     * Get all available user registry keys.
     *
     * Example: ['company', 'deal', 'task', 'user']
     *
     * @return string
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Get the API endpoint for a given user registry type.
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
