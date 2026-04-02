<?php

namespace App\Traits\Activity;

use App\Models\Attachment;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Defines the relationships for the Activity model.
 *
 * This trait encapsulates all Eloquent relationship methods for the Activity
 * model, including associations to users (assigned, creator, updater, deleter,
 * restorer), polymorphic subject models, attachments, tasks, and notes.
 *
 * By using this trait within the Activity model, we maintain a clean
 * separation of concerns, keeping relationship definitions organized
 * and easily maintainable.
 * The relationships defined here allow for intuitive access to related
 * models, enabling developers to easily navigate between activities and
 * their associated users, subjects, attachments, tasks, and notes.
 *
 * Relationships defined in this trait include:
 * - user(): BelongsTo<User,Activity> - The user assigned to the activity.
 * - subject(): MorphTo<Model,Activity> - The polymorphic subject model
 *      associated with the activity.
 * - creator(): BelongsTo<User,Activity> - The user who created the activity.
 * - updater(): BelongsTo<User,Activity> - The user who last updated
 *      the activity.
 * - deleter(): BelongsTo<User,Activity> - The user who deleted the activity.
 * - restorer(): BelongsTo<User,Activity> - The user who restored the activity.
 * - attachments(): MorphMany<Attachment> - All attachments associated
 *      with the activity.
 * - tasks(): MorphMany<Task> - All tasks associated with the activity.
 * - notes(): MorphMany<Note> - All notes associated with the activity.
 *
 * Note: The actual implementation of these relationships
 * is provided in the trait, and they are intended to be
 * used as part of the Activity model's functionality.
 * This trait should be used in conjunction with the
 * ActivityHelpers and ActivityScopes traits to provide
 * a complete set of features for the Activity model.
 * The Activity model itself should also use the HasTestPrefix
 * trait to enable test prefixing functionality for the
 * activity type.
 * The relationships defined here can be used in Eloquent
 * queries and model interactions to easily access related
 * data, improving the efficiency and clarity of code that
 * interacts with activities and their associated models.
 * Example usage of these relationships in code might look like:
 * ```php
 * $activity = Activity::find(1);
 * $user = $activity->user; // Get the assigned user
 * $subject = $activity->subject; // Get the associated subject model
 * $creator = $activity->creator; // Get the user who created
 * the activity
 * $attachments = $activity->attachments; // Get all attachments
 * for the activity
 * $tasks = $activity->tasks; // Get all tasks for the activity
 * $notes = $activity->notes; // Get all notes for the activity
 * ```
 */
trait ActivityRelationships
{
    /**
     * Get the user that is assigned the activity.
     *
     * The assigned user is optional and may be null if the
     * activity is not currently assigned to anyone.
     * This relationship allows for easy retrieval of the
     * user responsible for the activity, if applicable.
     *
     * @return BelongsTo<User,Activity>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the polymorphic subject model this activity belongs to.
     *
     * The subject may be a Company, Deal, Task, or User as defined
     * in ACTIVITY_TYPES.
     *
     * This relationship allows the activity to be associated with any model
     * that implements the polymorphic interface, providing flexibility in
     * how activities are linked to various entities within the CRM. The
     * subject model can be accessed directly from the activity, enabling
     * developers to easily navigate from an activity to its associated
     * subject, regardless of the specific model type.
     *
     * @return MorphTo<Model,Activity>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that created the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for creating the activity record.
     *
     * @return BelongsTo<User,Activity>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for the most recent update to the
     * activity record.
     *
     * @return BelongsTo<User,Activity>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for deleting the activity record,
     * if it has been soft-deleted.
     *
     * @return BelongsTo<User,Activity>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the activity.
     *
     * This relationship allows for easy retrieval of the
     * user responsible for restoring the activity record,
     * if it has been soft-deleted and subsequently restored.
     *
     * @return BelongsTo<User,Activity>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the activity.
     *
     * This relationship allows for easy retrieval of all attachments
     * linked to the activity, enabling developers to access related
     * files and documents directly from the activity model. Attachments
     * can be added to activities to provide additional context or
     * information, and this relationship facilitates seamless
     * access to those attachments.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all tasks associated with the activity.
     *
     * This relationship allows for easy retrieval of all tasks
     * linked to the activity, enabling developers to access related
     * tasks directly from the activity model. Tasks can be created in
     * relation to activities to track specific actions or follow-ups,
     * and this relationship facilitates seamless access to those tasks
     * for better activity management and workflow integration.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the activity.
     *
     * This relationship allows for easy retrieval of all notes linked
     * to the activity, enabling developers to access related notes
     * directly from the activity model. Notes can be added to activities
     * to provide additional context, comments, or information, and
     * this relationship facilitates seamless access to those notes
     * for better activity documentation and communication.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
