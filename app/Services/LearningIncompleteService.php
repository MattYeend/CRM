<?php

namespace App\Services;

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
        $learning->update([
            'is_completed' => false,
            'completed_by' => null,
            'completed_at' => null,
        ]);

        return $learning;
    }
}
