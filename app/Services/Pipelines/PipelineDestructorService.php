<?php

namespace App\Services\Pipelines;

use App\Models\Pipeline;

/**
 * Handles soft deletion and restoration of Pipeline records.
 *
 * Writes audit fields before delegating to Eloquent's soft-delete and
 * restore methods, ensuring the deleted_by, deleted_at, restored_by, and
 * restored_at columns are always populated.
 */
class PipelineDestructorService
{
    /**
     * Soft-delete a pipeline.
     *
     * Records the authenticated user and timestamp in the audit columns
     * before soft-deleting the pipeline.
     *
     * @param  Pipeline $pipeline The pipeline instance to soft-delete.
     *
     * @return void
     */
    public function destroy(Pipeline $pipeline): void
    {
        $userId = auth()->id();

        $pipeline->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
        ]);

        $pipeline->delete();
    }

    /**
     * Restore a soft-deleted pipeline.
     *
     * Looks up the pipeline including trashed records, records the
     * authenticated user and timestamp in the audit columns, then restores
     * the pipeline. Returns the pipeline unchanged if it is not currently
     * trashed.
     *
     * @param  int $id The primary key of the soft-deleted pipeline.
     *
     * @return Pipeline The restored pipeline instance.
     */
    public function restore(int $id): Pipeline
    {
        $userId = auth()->id();
        $pipeline = Pipeline::withTrashed()->findOrFail($id);

        if ($pipeline->trashed()) {
            $pipeline->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $pipeline->restore();
        }

        return $pipeline;
    }
}
