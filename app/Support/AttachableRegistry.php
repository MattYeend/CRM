<?php

namespace App\Support;

class AttachableRegistry
{
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

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function endpoint(string $type): ?string
    {
        return self::all()[$type]['endpoint'] ?? null;
    }
}
