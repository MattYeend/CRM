<?php

namespace App\Services;

use App\Models\Pipeline;

class PipelineDestructorService
{
    /**
     * Soft-delete a pipeline.
     *
     * @param Pipeline $pipeline
     *
     * @return void
     */
    public function destroy(Pipeline $pipeline): void
    {
        $pipeline->update([
            'deleted_by' => auth()->id(),
        ]);

        $pipeline->delete();
    }

    /**
     * Restore a trashed pipeline.
     *
     * @param int $id
     *
     * @return Pipeline
     */
    public function restore(int $id): Pipeline
    {
        $pipeline = Pipeline::withTrashed()->findOrFail($id);

        if ($pipeline->trashed()) {
            $pipeline->restore();
        }

        return $pipeline;
    }
}
