<?php

namespace App\Services\Learnings;

use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use Illuminate\Support\Facades\DB;

class LearningUpdaterService
{
    public function __construct(
        private RecreateLearningQuestionsService $questionsService,
    ) {
        $this->questionsService = $questionsService;
    }
    /**
     * Update the learning using request data.
     *
     * @param UpdateLearningRequest $request
     *
     * @param Learning $learning
     *
     * @return Learning
     */
    public function update(
        UpdateLearningRequest $request,
        Learning $learning
    ): Learning {
        $user = $request->user();

        $data = $request->validated();

        return DB::transaction(function () use ($learning, $data, $user) {
            $learning->update([
                ...$data,
                'updated_by' => $user->id,
                'updated_at' => now(),
            ]);

            if (isset($data['questions'])) {
                $this->questionsService->recreate(
                    $learning,
                    $data['questions']
                );
            }
            return $learning->load('questions.answers');
        });
    }

    /**
     * Helper function to recreate questions
     *
     * @param Learning $learning
     *
     * @param array $questions
     */
    private function recreateQuestions(
        Learning $learning,
        array $questions
    ): void {
        $learning->questions()->each(function ($q) {
            $q->answers()->delete();
            $q->delete();
        });

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
