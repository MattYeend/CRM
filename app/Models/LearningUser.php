<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model representing the many-to-many relationship between learnings
 * and users.
 *
 * Tracks each user's completion state and timestamp for an assigned learning,
 * along with the standard audit tracking columns.
 */
class LearningUser extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'learning_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'learning_id',
        'user_id',
        'is_complete',
        'completed_at',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
