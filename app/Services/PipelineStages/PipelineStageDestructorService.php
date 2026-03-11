<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;

class PipelineStageDestructorService
{
    /**
     * Soft-delete a pipeline stage.
     *
     * @param PipelineStage $pipelineStage
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
     * Restore a trashed pipeline stage.
     *
     * @param int $id
     *
     * @return PipelineStage
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
