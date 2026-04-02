<?php

namespace App\Services\Learnings;

use App\Models\Learning;

/**
 * Handles marking a learning as incomplete for the authenticated user.
 *
 * Updates the pivot table between users and learnings by clearing
 * completion status and timestamp.
 */
class LearningIncompleteService
{
    /**
     * Revert a learning to incomplete for the authenticated user.
     *
     * @param  Learning $learning
     *
     * @return Learning
     */
    public function incomplete(Learning $learning): Learning
    {
        $userId = auth()->id();

        $learning->users()->updateExistingPivot($userId, [
            'is_complete'  => false,
            'completed_at' => null,
            'score'        => null,
            'updated_by'   => $userId,
        ]);

        return $learning->fresh();
    }
}
