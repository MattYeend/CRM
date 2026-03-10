<?php

namespace App\Services;

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
        $pipelineStage->update([
            'deleted_by' => auth()->id(),
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
        $pipelineStage = PipelineStage::withTrashed()->findOrFail($id);

        if ($pipelineStage->trashed()) {
            $pipelineStage->restore();
        }

        return $pipelineStage;
    }
}
