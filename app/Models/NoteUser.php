<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model representing the many-to-many relationship between notes
 * and users.
 *
 * Tracks the association between a note and a user, along with standard
 * audit and test tracking columns.
 *
 * Relationships defined in this model include:
 * - note(): The note that this user association belongs to.
 * - user(): The user that this note association belongs to.
 * - creator(): The user that created this note-user association.
 * - updater(): The user that last updated this note-user association.
 * - deleter(): The user that deleted this note-user association (if soft-deleted).
 * - restorer(): The user that restored this note-user association (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $noteUser = NoteUser::find(1);
 * $note = $noteUser->note; // Get the associated note
 * $user = $noteUser->user; // Get the associated user
 * $creator = $noteUser->creator; // Get the user that created this association
 * $updater = $noteUser->updater; // Get the user that last updated this association
 * $deleter = $noteUser->deleter; // Get the user that deleted this association (if applicable)
 * $restorer = $noteUser->restorer; // Get the user that restored this association (if applicable)
 * ```
 *
 * Query scopes include:
 * - scopeReal($query): Filter the query to only include non-test note-user associations.
 * Example usage of query scopes:
 * ```php
 * $realNoteUsers = NoteUser::real()->get(); // Get all non-test note-user associations
 * ```
 */
class NoteUser extends Pivot
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'note_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'note_id',
        'user_id',
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

    /**
     * Get the note that this user association belongs to.
     *
     * @return BelongsTo<Note,NoteUser>
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    /**
     * Get the user that this note association belongs to.
     *
     * @return BelongsTo<User,NoteUser>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that created this note-user association.
     *
     * @return BelongsTo<User,NoteUser>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated this note-user association.
     *
     * @return BelongsTo<User,NoteUser>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted this note-user association.
     *
     * @return BelongsTo<User,NoteUser>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored this note-user association.
     *
     * @return BelongsTo<User,NoteUser>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Scope a query to only include non-test records.
     *
     * @param Builder<NoteUser> $query The query builder instance.
     *
     * @return Builder<NoteUser> The modified query builder instance.
     */
    public function scopeReal($query)
    {
        return $query->where('is_test', false);
    }
}
