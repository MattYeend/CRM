<?php

namespace App\Services;

use App\Models\Attachment;

class AttachmentAttacher
{
    /**
     * Attach an attachment to a polymorphic model if resolvable.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @param string|null $type
     *
     * @param int|null $id
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
     * Resolve the model instance for the given type and id.
     * Returns null if resolution fails.
     *
     * @param string $type
     *
     * @param int$id
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
