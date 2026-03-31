<?php

namespace App\Services\Learnings;

use App\Models\Learning;

/**
 * Handles creation of questions and answers for a learning.
 *
 * Iterates through structured question data and persists
 * related questions and answers.
 */
class LearningQuestionsCreateService
{
    /**
     * Create questions and their answers for a learning.
     *
     * @param  Learning $learning The learning to attach questions to.
     * @param  array $questions The structured questions and answers data.
     *
     * @return void
     */
    public function create(Learning $learning, array $questions): void
    {
        foreach ($questions as $questionData) {
            $question = $learning->questions()->create([
                'question' => $questionData['question'] ?? null,
            ]);

            foreach ($questionData['answers'] ?? [] as $answerData) {
                $question->answers()->create([
                    'answer' => $answerData['answer'] ?? null,
                    'is_correct' => $answerData['is_correct'] ?? false,
                ]);
            }
        }
    }
}
