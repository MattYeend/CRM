<?php

namespace App\Services\Learnings;

use App\Http\Requests\StoreLearningRequest;
use App\Http\Requests\UpdateLearningRequest;
use App\Models\Learning;

/**
 * Orchestrates learning lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for learning create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class LearningManagementService
{
    /**
     * Service responsible for creating new learning records.
     *
     * @var LearningCreatorService
     */
    private LearningCreatorService $creator;

    /**
     * Service responsible for updating existing learning records.
     *
     * @var LearningUpdaterService
     */
    private LearningUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring learning records.
     *
     * @var LearningDestructorService
     */
    private LearningDestructorService $destructor;

    /**
     * Service responsible for completing learning records.
     *
     * @var LearningCompleteService
     */
    private LearningCompleteService $complete;

    /**
     * Service responsible for incompleting learning records.
     *
     * @var LearningIncompleteService
     */
    private LearningIncompleteService $incomplete;

    /**
     * Inject the required services into the management service.
     *
     * @param  LearningCreatorService $creator Handles learning creation.
     * @param  LearningUpdaterService $updater Handles learning updates.
     * @param  LearningDestructorService $destructor Handles learning deletion
     * and restoration.
     * @param  LearningCompleteService $complete Handles learning completion.
     * @param  LearningIncompleteService $incomplete Handlies learning
     * incompletion.
     */
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
     * @param  StoreLearningRequest $request Validated request
     * containing learning data.
     *
     * @return Learning The newly created learning.
     */
    public function store(StoreLearningRequest $request): Learning
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing learning.
     *
     * @param  UpdateLearningRequest $request Validated request containing
     * updated learning data.
     * @param  Learning $learning The learning instance to update.
     *
     * @return Learning The updated learning.
     */
    public function update(
        UpdateLearningRequest $request,
        Learning $learning
    ): Learning {
        return $this->updater->update($request, $learning);
    }

    /**
     * Soft-delete a learning.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Learning $learning The learning to delete.
     *
     * @return void
     */
    public function destroy(Learning $learning): void
    {
        $this->destructor->destroy($learning);
    }

    /**
     * Restore a soft-deleted learning.
     *
     * @param  int $id The primary key of the soft-deleted learning.
     *
     * @return Learning The restored learning.
     */
    public function restore(int $id): Learning
    {
        return $this->destructor->restore($id);
    }

    /**
     * Mark a learning as complete.
     *
     * Delegates to the completion service to update the user's
     * learning progress.
     *
     * @param  Learning $learning The learning to mark as complete.
     *
     * @return void
     */
    public function complete(Learning $learning): void
    {
        $this->complete->complete($learning);
    }

    /**
     * Mark a learning as incomplete.
     *
     * Delegates to the incomplete service to update the user's
     * learning progress.
     *
     * @param  Learning $learning The learning to mark as incomplete.
     *
     * @return void
     */
    public function incomplete(Learning $learning): void
    {
        $this->incomplete->incomplete($learning);
    }
}
