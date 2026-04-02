<?php

namespace App\Traits\User;

use App\Models\Activity;
use App\Models\Attachment;
use App\Models\Deal;
use App\Models\JobTitle;
use App\Models\Lead;
use App\Models\Learning;
use App\Models\LearningUser;
use App\Models\Note;
use App\Models\Order;
use App\Models\Role;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Provides relationship methods for the User model, defining how the user
 * is related to other entities in the system such as roles, job titles,
 * deals, tasks, notes, learnings, attachments, and activities.
 * This trait is intended to be used within the User model to encapsulate
 * all relationship definitions in one place for better organization and
 * maintainability.
 * Relationships defined in this trait include:
 * - role(): Defines a belongs-to relationship with the Role model,
 *      allowing access to the user's role information.
 * - jobTitle(): Defines a belongs-to relationship with the JobTitle model,
 *      allowing access to the user's job title information.
 * - deals(): Defines a has-many relationship with the Deal model, allowing
 *      access to all deals where the user is the owner.
 * - tasks(): Defines a has-many relationship with the Task model, allowing
 *      access to all tasks where the user is the assignee.
 * - notes(): Defines a has-many relationship with the Note model, allowing
 *      access to all notes created by the user.
 * - learnings(): Defines a many-to-many relationship with the Learning model
 *      through the LearningUser pivot model, allowing access to all learnings
 *      associated with the user along with additional pivot data.
 * - attachment(): Defines a polymorphic one-to-many relationship with the
 *      Attachment model, allowing access to all attachments where the user
 *      is the attachable entity.
 * - activity(): Defines a polymorphic one-to-many relationship with the
 *      Activity model, allowing access to all activities where the user is
 *      the subject entity.
 * - tasking(): Defines a polymorphic one-to-many relationship with the
 *      Task model, allowing access to all tasks where the user is the
 *      taskable entity.
 * - note(): Defines a polymorphic one-to-many relationship with the Note
 *      model, allowing access to all notes where the user is the notable
 *      entity.
 * - createdTasks(): Defines a has-many relationship with the Task model,
 *     allowing access to all tasks where the user is the creator.
 * - assignedTasks(): Defines a has-many relationship with the Task model,
 *      allowing access to all tasks where the user is the assignee.
 * - createdActivities(): Defines a has-many relationship with the Activity
 *      model, allowing access to all activities where the user is the
 *      creator.
 * - assignedActivities(): Defines a has-many relationship with the Activity
 *      model, allowing access to all activities where the user is the
 *      assignee.
 * - createdOrders(): Defines a has-many relationship with the Order model,
 *      allowing access to all orders where the user is the creator.
 * - ownedDeals(): Defines a has-many relationship with the Deal model, allowing
 *      access to all deals where the user is the owner.
 * - leads(): Defines a has-many relationship with the Lead model, allowing
 *      access to all leads where the user is the assignee.
 * - createdLearning(): Defines a has-many relationship with the Learning model,
 *      allowing access to all learning records where the user is the creator.
 * - assignedLearning(): Defines a many-to-many relationship with the Learning
 *      model through the LearningUser pivot model, allowing access to all
 *      learnings associated with the user along with additional pivot data,
 *      specifically focusing on the learnings assigned to the user as opposed
 *      to those created by the user.
 *
 * Note: The actual implementation of these relationships is
 * provided in the trait, and they are intended to be used as
 * part of the User model's functionality. This trait should
 * be used in conjunction with the UserHelpers trait to provide
 * a complete set of features for the User model. The User model
 * itself should also use the HasTestPrefix trait to enable
 * test prefixing functionality for user-related records.
 * The relationships defined here can be used in Eloquent queries
 * and model interactions to easily access related data, improving
 * the efficiency and clarity of code that interacts with users
 * and their associated models.
 * Example usage of these relationships in code might look like:
 * ```php
 * $user = User::find(1);
 * $role = $user->role; // Get the user's role
 * $jobTitle = $user->jobTitle; // Get the user's job title
 * $deals = $user->deals; // Get all deals owned by the user
 * $tasks = $user->tasks; // Get all tasks assigned to the user
 * $notes = $user->notes; // Get all notes created by the user
 * $learnings = $user->learnings; // Get all learnings associated
 * with the user
 * $attachments = $user->attachment; // Get all attachments for the user
 * $activities = $user->activity; // Get all activities for the user
 * $tasking = $user->tasking; // Get all tasks where the user is the
 * taskable entity
 * $note = $user->note; // Get all notes where the user is the notable
 * entity
 * $createdTasks = $user->createdTasks; // Get all tasks created by
 * the user
 * $assignedTasks = $user->assignedTasks; // Get all tasks assigned to
 * the user
 * $createdActivities = $user->createdActivities; // Get all activities
 * created by the user
 * $assignedActivities = $user->assignedActivities; // Get all activities
 * assigned to the user
 * $createdOrders = $user->createdOrders; // Get all orders created by
 * the user
 * $ownedDeals = $user->ownedDeals; // Get all deals owned by the user
 * $leads = $user->leads; // Get all leads assigned to the user
 * $createdLearning = $user->createdLearning; // Get all learning records
 * created by the user
 * $assignedLearning = $user->assignedLearning; // Get all learning records
 * assigned to the user
 * ```
 */
trait UserRelationships
{
    /**
     * Get the role assigned to the user.
     *
     * Defines a belongs-to relationship between the User and Role models,
     * allowing access to the user's role information.
     *
     * @return BelongsTo<Role,self>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the job title associated with the user.
     *
     * Defines a belongs-to relationship between the User and JobTitle models,
     * allowing access to the user's job title information.
     *
     * @return BelongsTo<JobTitle,self>
     */
    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    /**
     * Get the deals owned by the user.
     *
     * Defines a has-many relationship between the User and Deal models,
     * allowing access to all deals where the user is the owner.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * Get the tasks assigned to the user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the assignee.
     *
     * @return HasMany<Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the notes created by the user.
     *
     * Defines a has-many relationship between the User and Note models,
     * allowing access to all notes created by the user.
     *
     * @return HasMany<Note>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'user_id');
    }

    /**
     * Get the learnings associated with the user.
     *
     * Defines a many-to-many relationship between the User and Learning models
     * hrough the LearningUser pivot model. This allows access to all learnings
     * associated with the user, along with additional pivot data such as
     * completion status, timestamps, and metadata.
     * The pivot data includes:
     * - `is_complete`: Indicates whether the learning has been
     *      completed by the user.
     * - `user_id`: The ID of the user associated with the learning.
     * - `completed_at`: The timestamp when the learning was completed.
     * - `is_test`: Indicates whether the learning is a test.
     * - `meta`: Additional metadata related to the learning.
     * - `created_by`: The ID of the user who created the learning record.
     * - `updated_by`: The ID of the user who last updated the learning record.
     *
     * @return BelongsToMany<Learning>
     */
    public function learnings(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete',
                'user_id',
                'completed_at',
                'is_test',
                'meta',
                'created_by',
                'updated_by',
            ])
            ->withTimestamps();
    }

    /**
     * Get all attachments associated with the user.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Attachment models, allowing access to all attachments where
     * the user is the attachable entity. This means that the user can
     * have multiple attachments, and each attachment can belong to
     * different types of entities in the system (not just users).
     *
     * @return MorphMany<Attachment>
     */
    public function attachment(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the user.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Activity models, allowing access to all activities where the
     * user is the subject entity. This means that the user can have
     * multiple activities, and each activity can belong to different
     * types of entities in the system (not just users).
     *
     * @return MorphMany<Activity>
     */
    public function activity(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks where the user is the taskable entity.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Task models, allowing access to all tasks where the user is
     * the taskable entity. This means that the user can have multiple
     * tasks assigned to them as the taskable entity, and each task can
     * belong to different types of entities in the system
     * (not just users).
     *
     * @return MorphMany<Task>
     */
    public function tasking(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the user as a notable entity.
     *
     * Defines a polymorphic one-to-many relationship between the User
     * and Note models, allowing access to all notes where the user is
     * the notable entity. This means that the user can have multiple
     * notes associated with them as the notable entity, and each note
     * can belong to different types of entities in the system
     * (not just users).
     *
     * @return MorphMany<Note>
     */
    public function note(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Tasks created by this user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the creator. This
     * relationship specifically focuses on the tasks that were created
     * by the user, as opposed to those assigned to the user or where
     * the user is the taskable entity.
     *
     * @return HasMany<Task>
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Tasks assigned to this user.
     *
     * Defines a has-many relationship between the User and Task models,
     * allowing access to all tasks where the user is the assignee. This
     * relationship specifically focuses on the tasks that are assigned
     * to the user, as opposed to those created by the user or where the
     * user is the taskable entity.
     *
     * @return HasMany<Task>
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Activities created by this user.
     *
     * Defines a has-many relationship between the User and Activity models,
     * allowing access to all activities where the user is the creator.
     * This relationship specifically focuses on the activities that were
     * created by the user, as opposed to those assigned to the user or
     * where the user is the subject of the activity.
     *
     * @return HasMany<Activity>
     */
    public function createdActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }

    /**
     * Activities assigned to this user.
     *
     * Defines a has-many relationship between the User and Activity models,
     * allowing access to all activities where the user is the assignee.
     * This relationship specifically focuses on the activities that are
     * assigned to the user, as opposed to those created by the user or
     * where the user is the subject of the activity.
     *
     * @return HasMany<Activity>
     */
    public function assignedActivities(): HasMany
    {
        return $this->hasMany(Activity::class, 'assigned_to');
    }

    /**
     * Orders created by this user.
     *
     * Defines a has-many relationship between the User and Order models,
     * allowing access to all orders where the user is the creator. This
     * relationship specifically focuses on the orders that were created
     * by the user, as opposed to those assigned to the user or owned
     * by the user.
     *
     * @return HasMany<Order>
     */
    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    /**
     * Deals owned by this user.
     *
     * Defines a has-many relationship between the User and Deal models,
     * allowing access to all deals where the user is the owner. This
     * relationship specifically focuses on the deals that are owned by
     * the user, as opposed to those created by the user or assigned to
     * the user.
     *
     * @return HasMany<Deal>
     */
    public function ownedDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    /**
     * Leads assigned to this user.
     *
     * Defines a has-many relationship between the User and Lead models,
     * allowing access to all leads where the user is the assignee.
     * This relationship specifically focuses on the leads that are
     * assigned to the user, as opposed to those created by the user
     * or owned by the user.
     *
     * @return HasMany<Lead>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    /**
     * Learning records created by this user.
     *
     * Defines a has-many relationship between the User and Learning
     * models, allowing access to all learning records where the user
     * is the creator. This relationship specifically focuses on the
     * learnings that were created by the user, as opposed to those
     * assigned to the user.
     *
     * @return HasMany<Learning>
     */
    public function createdLearning(): HasMany
    {
        return $this->hasMany(Learning::class, 'created_by');
    }

    /**
     * Learning records assigned to this user.
     *
     * Defines a many-to-many relationship between the User and
     * Learning models through the LearningUser pivot model,
     * allowing access to all learnings associated with the user
     * along with additional pivot data such as completion status,
     * timestamps, and metadata. This relationship specifically
     * focuses on the learnings that are assigned to the user,
     * as opposed to those created by the user.
     *
     * @return BelongsToMany<Learning>
     */
    public function assignedLearning(): BelongsToMany
    {
        return $this->belongsToMany(Learning::class)
            ->using(LearningUser::class)
            ->withPivot([
                'is_complete',
            ])
            ->withTimestamps();
    }
}
