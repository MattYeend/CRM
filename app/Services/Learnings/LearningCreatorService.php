<?php

namespace App\Services\Learnings;

use App\Http\Requests\StoreLearningRequest;
use App\Models\Learning;
use Illuminate\Support\Facades\DB;

class LearningCreatorService
{
    public function __construct(
        private CreateLearningQuestionsService $questionsService,
    ) {
        $this->questionsService = $questionsService;
    }

    /**
     * Create a new learning from request data.
     *
     * @param StoreLearningRequest $request
     *
     * @return Learning
     */
    public function create(StoreLearningRequest $request): Learning
    {
        $user = $request->user();
        $data = $request->validated();

        return DB::transaction(function () use ($data, $user) {
            $learning = Learning::create([
                ...$data,
                'created_by' => $user->id,
                'created_at' => now(),
            ]);

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
