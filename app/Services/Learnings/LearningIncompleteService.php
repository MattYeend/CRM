<?php

namespace App\Services\Learnings;

use App\Models\Learning;

class LearningIncompleteService
{
    /**
     * Incomplete a learning
     *
     * @param Learning
     *
     * @return Learning
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
