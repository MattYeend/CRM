<?php

namespace App\Services\Learnings;

use App\Models\Learning;

class LearningQuestionsCreateService
{
    /**
     * Create questions and answers for a learning.
     *
     * @param Learning $learning
     *
     * @param array $questions
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
