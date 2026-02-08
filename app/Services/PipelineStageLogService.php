<?php

namespace App\Services;

use App\Models\Log;
use App\Models\PipelineStage;
use App\Models\User;

class PipelineStageLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Pipeline Stage.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PipelineStage $pipelineStage The pipeline stage was created.
     *
     * @return Log The created log entry.
     */
    public function pipelineStageCreated(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'created_at' => $pipelineStage->created_at,
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
     * Log the update of a Pipeline Stage.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PipelineStage $pipelineStage The pipeline stage was updated.
     *
     * @return Log The created log entry.
     */
    public function pipelineStageUpdated(
        User $user,
        int $userId,
        PipelineStage $pipelineStage
    ): array {
        $data = $this->basePipelineStageData($pipelineStage) + [
            'updated_at' => $pipelineStage->updated_at,
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
     * Log the deletion of a Pipeline Stage.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PipelineStage $pipelineStage The pipeline stage was deleted.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Pipeline Stage.
     *
     * @param User $user The user being logged.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param PipelineStage $pipelineStage The pipeline stage was restored.
     *
     * @return Log The created log entry.
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
     * Prepare the base data for logging a Pipeline Stage.
     *
     * @param PipelineStage $pipelineStage The pipeline stage being logged.
     *
     * @return array The base data for the log entry.
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
