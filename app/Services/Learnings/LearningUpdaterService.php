<?php

namespace App\Services\Learnings;

use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;
use Illuminate\Support\Facades\DB;

/**
 * Handles updates to Learning records.
 *
 * This service orchestrates:
 * - Updating the Learning model
 * - Syncing assigned users
 * - Recreating learning questions
 */
class LearningUpdaterService
{
    /**
     * Service responsible for recreating learning questions.
     *
     * @var LearningQuestionsRecreateService
     */
    private LearningQuestionsRecreateService $questionsService;

    /**
     * Service responsible for syncing users assigned to a learning.
     *
     * @var LearningUserSyncService
     */
    private LearningUserSyncService $userSyncService;

    /**
     * Create a new service instance.
     *
     * @param LearningQuestionsRecreateService $questionsService
     * @param LearningUserSyncService $userSyncService
     */
    public function __construct(
        LearningQuestionsRecreateService $questionsService,
        LearningUserSyncService $userSyncService,
    ) {
        $this->questionsService = $questionsService;
        $this->userSyncService = $userSyncService;
    }

    /**
     * Update an existing Learning record.
     *
     * @param UpdateLearningRequest $request
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
            $this->updateLearning($learning, $data, $user->id);

            if (isset($data['users'])) {
                $this->syncUsers($learning, $data['users'], $user->id);
            }

            if (isset($data['questions']) && count($data['questions']) > 0) {
                $this->recreateQuestions($learning, $data['questions']);
            }

            return $learning->load('questions.answers');
        });
    }

    /**
     * Update base learning model.
     *
     * @param Learning $learning
     * @param array $data
     * @param int $userId
     *
     * @return void
     */
    private function updateLearning(
        Learning $learning,
        array $data,
        int $userId
    ): void {
        $learning->update([
            ...$data,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Sync users to learning.
     *
     * @param Learning $learning
     * @param array $users
     * @param int $userId
     *
     * @return void
     */
    private function syncUsers(
        Learning $learning,
        array $users,
        int $userId
    ): void {
        $this->userSyncService->sync($learning, $users, $userId);
    }

    /**
     * Recreate all questions for a learning.
     *
     * @param Learning $learning
     * @param array $questions
     *
     * @return void
     */
    private function recreateQuestions(
        Learning $learning,
        array $questions
    ): void {
        $this->questionsService->recreate($learning, $questions);
    }
}
