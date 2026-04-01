<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;

/**
 * Handles soft deletion and restoration of PipelineStage records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PipelineStageDestructorService
{
    /**
     * Soft-delete a pipeline stage.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the pipeline stage.
     *
     * @param  PipelineStage $pipelineStage The pipeline stage instance to soft-delete.
     *
     * @return void
     */
    public function destroy(PipelineStage $pipelineStage): void
    {
        $userId = auth()->id();

        $pipelineStage->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $pipelineStage->delete();
    }

    /**
     * Restore a soft-deleted pipeline stage.
     *
     * Looks up the pipeline stage including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the pipeline stage. Returns the pipeline stage unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted pipeline stage.
     *
     * @return PipelineStage The restored pipeline stage instance.
     */
    public function restore(int $id): PipelineStage
    {
        $userId = auth()->id();

        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);

        if ($pipelineStage->trashed()) {
            $pipelineStage->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);

            $pipelineStage->restore();
        }

        return $pipelineStage;
    }
}
