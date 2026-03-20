<?php

namespace App\Services\Learnings;

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
            'is_complete' => true,
            'completed_at' => now(),
        ]);

        return $learning;
    }
}
