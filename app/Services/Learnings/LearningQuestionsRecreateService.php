<?php

namespace App\Services\Learnings;

use App\Models\Learning;

class LearningQuestionsRecreateService
{
    /**
     * Recreate questions and answers for a learning.
     *
     * @param Learning $learning
     *
     * @param array $questions
     *
     * @return void
     */
    public function recreate(Learning $learning, array $questions): void
    {
        $this->deleteExisting($learning);

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

    /**
     * Delete existing questions and answers for a learning.
     *
     * @param Learning $learning
     *
     * @return void
     */
    private function deleteExisting(Learning $learning): void
    {
        $learning->questions()->each(function ($q) {
            $q->answers()->delete();
            $q->delete();
        });
    }
}
