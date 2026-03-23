<?php

namespace App\Services\Learnings;

use App\Models\Learning;

class LearningCompleteService
{
    /**
     * Complete a learning
     *
     * @param Learning
     *
     * @return Learning
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
