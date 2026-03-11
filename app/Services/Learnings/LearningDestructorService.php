<?php

namespace App\Services\Learnings;

use App\Models\Learning;

class LearningDestructorService
{
    /**
     * Soft-delete a learning.
     *
     * @param Learning $learning
     *
     * @return void
     */
    public function destroy(Learning $learning): void
    {
        $learning->update([
            'deleted_by' => auth()->id(),
        ]);

        $learning->delete();
    }

    /**
     * Restore a trashed learning.
     *
     * @param int $id
     *
     * @return Learning
     */
    public function restore(int $id): Learning
    {
        $learning = Learning::withTrashed()->findOrFail($id);

        if ($learning->trashed()) {
            $learning->restore();
        }

        return $learning;
    }
}
