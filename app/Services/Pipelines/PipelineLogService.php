<?php

namespace App\Services\Pipelines;

use App\Models\Log;
use App\Models\Pipeline;
use App\Models\User;

/**
 * Handles audit logging for Pipeline lifecycle events.
 *
 * Each public method writes a structured log entry via the Log model for
 * a specific pipeline action, combining base pipeline data with
 * action-specific timestamp and actor fields.
 */
class PipelineLogService
{
    /**
     * Log a pipeline creation event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Pipeline $pipeline The pipeline that was created.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineCreated(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'created_at' => now(),
            'created_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_CREATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline update event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Pipeline $pipeline The pipeline that was updated.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineUpdated(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'updated_at' => now(),
            'updated_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_UPDATED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline deletion event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Pipeline $pipeline The pipeline that was deleted.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineDeleted(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'deleted_at' => now(),
            'deleted_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_DELETED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Log a pipeline restoration event.
     *
     * @param  User $user The user who performed the action.
     * @param  int $userId The ID of the user who performed the action.
     * @param  Pipeline $pipeline The pipeline that was restored.
     *
     * @return array The structured data written to the log entry.
     */
    public function pipelineRestored(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'restored_at' => now(),
            'restored_by' => $user->name,
        ];

        Log::log(
            Log::ACTION_PIPELINE_RESTORED,
            $data,
            $userId,
        );

        return $data;
    }

    /**
     * Build the base data array shared across all pipeline log entries.
     *
     * @param  Pipeline $pipeline The pipeline being logged.
     *
     * @return array The base fields extracted from the pipeline.
     */
    protected function basePipelineData(Pipeline $pipeline): array
    {
        return [
            'id' => $pipeline->id,
            'name' => $pipeline->name,
            'description' => $pipeline->description,
            'is_default' => $pipeline->is_default,
        ];
    }
}
