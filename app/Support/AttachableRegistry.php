<?php

namespace App\Support;

/**
 * Registry for all attachable entity types used across the application.
 *
 * Provides a central source of truth for available attachable models,
 * their labels, and associated API endpoints.
 */
class AttachableRegistry
{
    /**
     * Get all attachable types with their metadata.
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
     * Get all available attachable type keys.
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
     * Get the API endpoint for a given attachable type.
     *
     * @param string $type The attachable type key (e.g. 'company')
     *
     * @return string|null The endpoint URL, or null if type does not exist
     */
    public static function endpoint(string $type): ?string
    {
        return self::all()[$type]['endpoint'] ?? null;
    }
}
