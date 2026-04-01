<?php

namespace App\Services\PipelineStages;

use App\Http\Requests\StorePipelineStageRequest;
use App\Http\Requests\UpdatePipelineStageRequest;
use App\Models\PipelineStage;

/**
 * Orchestrates pipeline stage lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for pipeline stage create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PipelineStageManagementService
{
    /**
     * Service responsible for creating new pipeline stage records.
     *
     * @var PipelineStageCreatorService
     */
    private PipelineStageCreatorService $creator;

    /**
     * Service responsible for updating existing pipeline records.
     *
     * @var PipelineStageUpdaterService
     */
    private PipelineStageUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring pipeline stage records.
     *
     * @var PipelineStageDestructorService
     */
    private PipelineStageDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PipelineStageCreatorService $creator Handles pipeline stage creation.
     * @param  PipelineStageUpdaterService $updater Handles pipeline stage updates.
     * @param  PipelineStageDestructorService $destructor Handles pipeline stage deletion
     * and restoration.
     */
    public function __construct(
        PipelineStageCreatorService $creator,
        PipelineStageUpdaterService $updater,
        PipelineStageDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new pipeline stage.
     *
     * @param  StorePipelineStageRequest $request Validated request containing pipeline stage
     * data.
     *
     * @return PipelineStage The newly created pipeline stage.
     */
    public function store(StorePipelineStageRequest $request): PipelineStage
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing pipeline stage.
     *
     * @param  UpdatePipelineStageRequest $request Validated request containing
     * updated pipeline stage data.
     * @param  PipelineStage $pipelineStage The pipeline stage instance to update.
     *
     * @return PipelineStage The updated pipeline stage.
     */
    public function update(
        UpdatePipelineStageRequest $request,
        PipelineStage $pipelineStage
    ): PipelineStage {
        return $this->updater->update($request, $pipelineStage);
    }

    /**
     * Soft-delete a pipeline stage.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  PipelineStage $pipelineStage The pipeline stage to delete.
     *
     * @return void
     */
    public function destroy(PipelineStage $pipelineStage): void
    {
        $this->destructor->destroy($pipelineStage);
    }

    /**
     * Restore a soft-deleted pipeline stage.
     *
     * @param  int $id The primary key of the soft-deleted pipeline stage.
     *
     * @return PipelineStage The restored pipeline stage.
     */
    public function restore(int $id): PipelineStage
    {
        return $this->destructor->restore($id);
    }
}
