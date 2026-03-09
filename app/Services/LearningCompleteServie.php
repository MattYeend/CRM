<?php

namespace App\Services;

use App\Models\Learning;

class LearningCompleteServie
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
        $learning->update([
            'is_completed' => true,
            'completed_by' => auth()->id,
            'completed_at' => now(),
        ]);

        return $learning;
    }
}
