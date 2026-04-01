<?php

namespace App\Services\Pipelines;

use App\Http\Requests\StorePipelineRequest;
use App\Http\Requests\UpdatePipelineRequest;
use App\Models\Pipeline;

/**
 * Orchestrates pipeline lifecycle operations by delegating to focused
 * sub-services.
 *
 * Acts as the single entry point for pipeline create, update, delete, and
 * restore operations, keeping controllers decoupled from the underlying
 * service implementations.
 */
class PipelineManagementService
{
    /**
     * Service responsible for creating new pipeline records.
     *
     * @var PipelineCreatorService
     */
    private PipelineCreatorService $creator;

    /**
     * Service responsible for updating existing pipeline records.
     *
     * @var PipelineUpdaterService
     */
    private PipelineUpdaterService $updater;

    /**
     * Service responsible for soft-deleting and restoring pipeline records.
     *
     * @var PipelineDestructorService
     */
    private PipelineDestructorService $destructor;

    /**
     * Inject the required services into the management service.
     *
     * @param  PipelineCreatorService $creator Handles pipeline creation.
     * @param  PipelineUpdaterService $updater Handles pipeline updates.
     * @param  PipelineDestructorService $destructor Handles pipeline deletion
     * and restoration.
     */
    public function __construct(
        PipelineCreatorService $creator,
        PipelineUpdaterService $updater,
        PipelineDestructorService $destructor,
    ) {
        $this->creator = $creator;
        $this->updater = $updater;
        $this->destructor = $destructor;
    }

    /**
     * Create a new pipeline.
     *
     * @param  StorePipelineRequest $request Validated request containing
     * pipeline data.
     *
     * @return Pipeline The newly created pipeline.
     */
    public function store(StorePipelineRequest $request): Pipeline
    {
        return $this->creator->create($request);
    }

    /**
     * Update an existing pipeline.
     *
     * @param  UpdatePipelineRequest $request Validated request containing
     * updated pipeline data.
     * @param  Pipeline $pipeline The pipeline instance to update.
     *
     * @return Pipeline The updated pipeline.
     */
    public function update(
        UpdatePipelineRequest $request,
        Pipeline $pipeline
    ): Pipeline {
        return $this->updater->update($request, $pipeline);
    }

    /**
     * Soft-delete a pipeline.
     *
     * Delegates to the destructor service to perform a soft-delete.
     *
     * @param  Pipeline $pipeline The pipeline to delete.
     *
     * @return void
     */
    public function destroy(Pipeline $pipeline): void
    {
        $this->destructor->destroy($pipeline);
    }

    /**
     * Restore a soft-deleted pipeline.
     *
     * @param  int $id The primary key of the soft-deleted pipeline.
     *
     * @return Pipeline The restored pipeline.
     */
    public function restore(int $id): Pipeline
    {
        return $this->destructor->restore($id);
    }
}
