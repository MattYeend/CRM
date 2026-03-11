<?php

namespace App\Services\Pipelines;

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
        $userId = auth()->id();

        $pipeline->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
