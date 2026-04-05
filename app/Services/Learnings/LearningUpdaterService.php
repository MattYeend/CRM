<?php

namespace App\Services\Learnings;

use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use Illuminate\Support\Facades\DB;

/**
 * Handles updates to Learning records.
 *
 * Validates incoming request data, assigns audit fields, and persists
 * updates to the learning.
 */
class LearningUpdaterService
{
    /**
     * Service responsible for updating existing learning questions.
     *
     * @var LearningQuestionsRecreateService
     */
    private LearningQuestionsRecreateService $questionsService;

    /**
     * Inject the required services into the management service.
     *
     * @param  LearningQuestionsRecreateService $questionsService
     * Handles learning question updates.
     */
    public function __construct(
        LearningQuestionsRecreateService $questionsService,
    ) {
        $this->questionsService = $questionsService;
    }
    /**
     * Update an existing learning.
     *
     * Extracts validated data from the request, assigns the authenticated
     * user and timestamp to audit fields, updates the learning, and returns
     * a fresh instance.
     *
     * @param  UpdateLearningRequest $request The request containing
     * validated learning data.
     * @param  Learning $learning The learning to update.
     *
     * @return Learning The updated learning instance.
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
