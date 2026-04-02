<?php

namespace App\Services\Learnings;

use App\Models\Learning;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles marking a learning as completed for the authenticated user.
 *
 * Updates the pivot table between users and learnings by setting
 * completion status and timestamp.
 */
class LearningCompleteService
{
    /**
     * Mark a learning as complete for the authenticated user.
     *
     * If the learning has a pass_score set, the user's score on the pivot
     * must meet or exceed it. Throws a 422 if the score is insufficient.
     *
     * @param  Learning $learning
     * @param  int|null $score The user's score (0–100), if applicable.
     * @return Learning
     *
     * @throws HttpException
     */
    public function complete(Learning $learning, ?int $score = null): Learning
    {
        $userId = auth()->id();

        if (
            $learning->pass_score !== null
            && ($score === null || $score < $learning->pass_score)
        ) {
            abort(422, 'Score of ' . $score . ' does not meet the pass score of ' . $learning->pass_score . '.');
        }
    
        $learning->users()->updateExistingPivot($userId, [
            'is_complete' => true,
            'completed_at' => now(),
            'score' => $score,
            'updated_by' => $userId,
        ]);

        return $learning;
    }
}
