<?php

namespace App\Services;

use App\Models\Log;
use App\Models\Pipeline;
use App\Models\User;

class PipelineLogService
{
    public function __construct()
    {
        // Empty constructor
    }

    /**
     * Log the creation of a Pipeline.
     *
     * @param User $user The user that created the pipeline.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Pipeline $pipeline The pipeline being logged.
     *
     * @return Log The created log entry.
     */
    public function pipelineCreated(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'created_at' => $pipeline->created_at,
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
     * Log the update of a Pipeline.
     *
     * @param User $user The user that updated the pipeline.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Pipeline $pipeline The pipeline being logged.
     *
     * @return Log The created log entry.
     */
    public function pipelineUpdated(
        User $user,
        int $userId,
        Pipeline $pipeline
    ): array {
        $data = $this->basePipelineData($pipeline) + [
            'updated_at' => $pipeline->updated_at,
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
     * Log the deletion of a Pipeline.
     *
     * @param User $user The user that deleted the pipeline.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Pipeline $pipeline The pipeline being logged.
     *
     * @return Log The created log entry.
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
     * Log the restoration of a Pipeline.
     *
     * @param User $user The user that restored the pipeline.
     *
     * @param int $userId The ID of the user who performed the action.
     *
     * @param Pipeline $pipeline The pipeline being logged.
     *
     * @return Log The created log entry.
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
     * Prepare base data for Pipeline logs.
     *
     * @param Pipeline $pipeline The pipeline being logged.
     *
     * @return array The base data for logging.
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
