<?php

namespace App\Services\PipelineStages;

use App\Models\Log;
use App\Models\PipelineStage;
use App\Models\User;

class PipelineStageLogService
{
    /**
     * Log a pipeline stage creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PipelineStage $pipelineStage The pipeline stage that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineStageCreated(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_STAGE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline stage update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PipelineStage $pipelineStage The pipeline stage that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineStageUpdated(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_STAGE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline stage deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PipelineStage $pipelineStage The pipeline stage that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineStageDeleted(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_STAGE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline stage restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  PipelineStage $pipelineStage The pipeline stage that
     * was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineStageRestored(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_STAGE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all pipeline stage log entries.
     *
     * @param  PipelineStage $pipelineStage The pipeline stage being logged.
     *
     * @return array The base fields extracted from the pipeline stage.
     */
    protected function basePipelineStageData(
        PipelineStage $pipelineStage
    ): array {
        return [
            'id' => $pipelineStage->id,
            'pipeline_id' => $pipelineStage->pipeline_id,
            'name' => $pipelineStage->name,
            'position' => $pipelineStage->position,
            'is_won_stage' => $pipelineStage->is_won_stage,
            'is_lost_stage' => $pipelineStage->is_lost_stage,
        ];
    }
}
