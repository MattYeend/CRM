<?php

namespace App\Services\Learnings;

use App\Http\Requests\StoreLearningRequest;
use App\Models\Learning;
use Illuminate\Support\Facades\DB;

/**
 * Handles the creation of new Learning records.
 *
 * Extracts validated data from the request, stamps the creator and
 * creation timestamp, and persists the new Learning.
 */
class LearningCreatorService
{
    /**
     * Service responsible for creating new learning questions.
     *
     * @var LearningQuestionsCreateService
     */
    private LearningQuestionsCreateService $questionsService;

    /**
     * Inject the required services into the management service.
     *
     * @param  LearningQuestionsCreateService $questionsService
     * Handles learning question creation.
     */
    public function __construct(
        LearningQuestionsCreateService $questionsService,
    ) {
        $this->questionsService = $questionsService;
    }

    /**
     * Create a new learning from the validated request data.
     *
     * Sets the created_by and created_at audit fields from the authenticated
     * user before persisting the record.
     *
     * @param  StoreLearningRequest $request Validated request containing
     * learning data.
     *
     * @return Learning The newly created learning record.
     */
    public function create(StoreLearningRequest $request): Learning
    {
        $user = $request->user();
        $data = $request->validated();

        return DB::transaction(function () use ($data, $user) {
            $learning = Learning::create([
                ...$data,
                'created_by' => $user->id,
            ]);

            if (! empty($data['users'])) {
                $learning->users()->sync(
                    collect($data['users'])->mapWithKeys(fn ($userId) => [
                        $userId => [
                            'is_complete' => false,
                            'score' => null,
                            'completed_at' => null,
                            'created_by' => $user->id,
                        ],
                    ])->toArray()
                );
            }

            if (isset($data['questions']) && count($data['questions']) > 0) {
                $this->questionsService->create(
                    $learning,
                    $data['questions']
                );
            }

            return $learning->load('questions.answers');
        });

        return Learning::create($data);
    }
}
