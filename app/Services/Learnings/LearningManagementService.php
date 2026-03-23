<?php

namespace App\Services\Learnings;

use App\Http\Requests\StoreLearningRequest;
use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;

class LearningManagementService
{
    private LearningCreatorService $creator;
    private LearningUpdaterService $updater;
    private LearningDestructorService $destructor;
    private LearningCompleteService $complete;
    private LearningIncompleteService $incomplete;

    public function __construct(
        LearningCreatorService $creator,
        LearningUpdaterService $updater,
        LearningDestructorService $destructor,
        LearningCompleteService $complete,
        LearningIncompleteService $incomplete,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
        $this->complete = $complete;
        $this->incomplete = $incomplete;
    }

    /**
     * Create a new learning.
     *
     * @param StoreLearningRequest $request
     *
     * @return Learning
     */
    public function store(StoreLearningRequest $request): Learning
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing learning.
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
        return $this->updater->update($request, $learning);
    }

    /**
     * Delete a learning (soft delete).
     *
     * @param Learning $learning
     *
     * @return void
     */
    public function destroy(Learning $learning): void
    {
        $this->destructor->destroy($learning);
    }

    /**
     * Restore a soft-deleted learning
     *
     * @param int $id
     *
     * @return Learning
     */
    public function restore(int $id): Learning
    {
        return $this->destructor->restore($id);
    }

    /**
     * Complete a learning
     *
     * @param Learning $learning
     *
     * @return void
     */
    public function complete(Learning $learning): void
    {
        $this->complete->complete($learning);
    }

    /**
     * Incomplete a learning
     *
     * @param Learning $learning
     *
     * @return void
     */
    public function incomplete(Learning $learning): void
    {
        $this->incomplete->incomplete($learning);
    }
}
