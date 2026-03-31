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
     * Mark a learning as incomplete for the current user.
     *
     * @param  Learning $learning The learning to mark as incomplete.
     *
     * @return Learning The updated learning instance.
     */
    public function incomplete(Learning $learning): Learning
    {
        $learning->users()->updateExistingPivot(auth()->id(), [
            'is_complete' => false,
            'completed_at' => null,
        ]);

        return $learning;
    }
}
