<?php

namespace App\Services\Attachments;

use App\Models\Attachment;

/**
 * Handles attaching Attachment models to polymorphic parent models.
 *
 * This service resolves a target model from a given type and identifier,
 * and associates the provided attachment via a polymorphic relationship
 * if the model can be successfully resolved.
 */
class AttachmentAttacherService
{
    /**
     * Attach an attachment to a polymorphic model if resolvable.
     *
     * Resolves the target model and associates the attachment if both
     * type and identifier are valid.
     *
     * @param  string|null  $type        The fully qualified model class name
     * @param  int|null     $id          The identifier of the target model
     * @param  Attachment   $attachment  The attachment model to associate
     *
     * @return void
     */
    public function attach(
        ?string $type,
        ?int $id,
        Attachment $attachment
    ): void {
        if ($type === null || $id === null) {
            return;
        }

        $model = $this->resolveModel($type, $id);

        if ($model) {
            $model->attachments()->save($attachment);
        }
    }

    /**
     * Resolve the model instance for the given type and identifier.
     *
     * Attempts to instantiate the model via the service container and
     * retrieve the record by its primary key. Returns null if resolution fails.
     *
     * @param  string  $type  The fully qualified model class name
     * @param  int     $id    The identifier of the model
     *
     * @return mixed|null
     */
    protected function resolveModel(string $type, int $id)
    {
        try {
            $candidate = app($type);

            return $candidate->find($id);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
