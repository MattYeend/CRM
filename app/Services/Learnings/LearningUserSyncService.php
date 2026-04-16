<?php

namespace App\Services\Learnings;

use App\Models\Learning;

/**
 * Handles syncing users to a Learning record.
 *
 * This service manages the many-to-many relationship between
 * Learnings and Users, ensuring pivot data is consistently set
 * whenever users are attached or updated.
 *
 * Pivot fields managed:
 * - is_complete (defaults to false)
 * - score (defaults to null)
 * - completed_at (defaults to null)
 * - created_by (tracks the user performing the sync)
 */
class LearningUserSyncService
{
    /**
     * Sync users to a learning with default pivot values.
     *
     * Each user is attached to the learning with reset progress data:
     * - Marks learning as not completed
     * - Clears any previous score
     * - Clears completion timestamp
     * - Sets creator metadata for audit tracking
     *
     * @param Learning $learning The learning instance to sync users with.
     * @param array $users Array of user IDs to attach to the learning.
     * @param int $userId ID of the authenticated user performing the sync.
     *
     * @return void
     */
    public function sync(Learning $learning, array $users, int $userId): void
    {
        $learning->users()->sync(
            collect($users)->mapWithKeys(fn ($id) => [
                $id => [
                    'is_complete' => false,
                    'score' => null,
                    'completed_at' => null,
                    'created_by' => $userId,
                ],
            ])->toArray()
        );
    }
}
