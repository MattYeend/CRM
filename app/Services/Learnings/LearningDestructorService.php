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
        $userId = auth()->id();

        $learning->update([
            'deleted_by' => $userId,
            'deleted_at' => now(),
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
        $userId = auth()->id();

        $learning = Learning::withTrashed()->findOrFail($id);

        if ($learning->trashed()) {
            $learning->update([
                'restored_by' => $userId,
                'restored_at' => now(),
            ]);
            $learning->restore();
        }

        return $learning;
    }
}
