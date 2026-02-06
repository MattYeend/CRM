<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Learning extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'date',
        'created_by',
        'updated_by',
        'deleted_by',
        'completed_by',
        'completed_at',
        'is_completed',
    ];

    /**
     * Get the user that created the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that completed the learning.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
