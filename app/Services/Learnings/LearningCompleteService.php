<?php

namespace App\Services\Learnings;

use App\Models\Learning;

/**
 * Handles marking a learning as completed for the authenticated user.
 *
 * Updates the pivot table between users and learnings by setting
 * completion status and timestamp.
 */
class LearningCompleteService
{
    /**
     * Mark a learning as complete for the current user.
     *
     * @param  Learning $learning The learning to mark as complete.
     *
     * @return Learning The updated learning instance.
     */
    public function complete(Learning $learning): Learning
    {
        $learning->users()->updateExistingPivot(auth()->id(), [
            'is_complete' => true,
            'completed_at' => now(),
        ]);

        return $learning;
    }
}
