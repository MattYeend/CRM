<?php

namespace App\Traits\Activity;

use Illuminate\Database\Eloquent\Builder;

/**
 * Defines query scopes for the Activity model.
 *
 * This trait encapsulates common query scopes
 * related to the Activity model, such as filtering
 * activities by assigned user, subject type,
 * specific subject instance, and excluding
 * test records.
 * By using this trait within the Activity model,
 * we maintain a clean separation of concerns, keeping
 * query scope definitions organized and easily
 * maintainable. The scopes defined here provide
 * convenient methods for filtering activity queries
 * based on common criteria, enhancing the readability
 * and usability of activity-related queries throughout
 * the application.
 *
 *  Scopes defined in this trait include:
 * - scopeAssignedTo($query, $userId): Scope a query to
 *      activities assigned to a specific user.
 * - scopeForSubjectType($query, $type): Scope a query
 *      to activities for a given subject type.
 * - scopeForSubject($query, $type, $id): Scope a query
 *      to activities for a specific subject instance.
 * - scopeReal($query): Scope a query to exclude test
 *      records.
 * Note: The actual implementation of these scopes is
 * provided in the trait, and they are intended to be
 * used as part of the Activity model's functionality.
 * This trait should be used in conjunction with the
 * ActivityHelpers and ActivityRelationships traits to
 * provide a complete set of features for the Activity
 * model. The Activity model itself should also use the
 * HasTestPrefix trait to enable test prefixing functionality
 * for the activity type.
 * The scopes defined here can be used in Eloquent queries to
 * easily filter activities based on the specified criteria,
 * improving the efficiency and clarity of database interactions
 * involving activities.
 * Example usage of these scopes in a query might look like:
 * ```php
 * $activities = Activity::assignedTo($userId)
 *    ->forSubjectType(Company::class)
 *   ->real()
 *   ->get();
 * ```
 */
trait ActivityScopes
{
    /**
     * Scope a query to activities assigned to a specific user.
     * This scope filters the query to include only activities
     * where the assigned_to field matches the provided
     * user ID, allowing for easy retrieval of all activities
     * assigned to a particular user in the system.
     *
     * @param  Builder<self> $query
     * @param  int $userId
     *
     * @return Builder<self>
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to activities for a given subject type.
     *
     * This scope filters the query to include only
     * activities where the subject_type field matches
     * the provided fully-qualified class name, allowing
     * for easy retrieval of all activities associated
     * with a specific type of subject model (e.g. Company,
     * Deal, Task, User) in the system.
     * The subject type is determined by the polymorphic
     * relationship defined in the Activity model, and this
     * scope provides a convenient way to filter activities
     * based on the type of their associated subject model.
     *
     * @param  Builder<self> $query
     * @param  string $type A fully-qualified class name (e.g. Company::class).
     *
     * @return Builder<self>
     */
    public function scopeForSubjectType(Builder $query, string $type): Builder
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope a query to activities for a specific subject instance.
     *
     * This scope filters the query to include only activities
     * where the subject_type field matches the provided
     * fully-qualified class name and the subject_id field
     * matches the provided primary key, allowing for easy
     * retrieval of all activities associated with a specific
     * instance of a subject model (e.g. a specific Company,
     * Deal, Task, or User) in the system. This scope is useful
     * for retrieving activities that are directly related to a
     * particular subject instance, providing a convenient way to
     * filter activities based on both their associated subject
     * type and specific subject instance.
     * The subject type and ID are determined by the polymorphic
     * relationship defined in the Activity model, and this scope
     * provides a convenient way to filter activities based on the
     * specific subject they are associated with.
     *
     * @param  Builder<self> $query
     * @param  string $type A fully-qualified class name.
     * @param  int $id   The subject model's primary key.
     *
     * @return Builder<self>
     */
    public function scopeForSubject(
        Builder $query,
        string $type,
        int $id
    ): Builder {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    /**
     * Scope a query to exclude test records.
     *
     * This scope filters the query to include only activities
     * where the 'is_test' attribute is false, effectively
     * excluding any activities that are marked as test records.
     * This is useful for ensuring that queries return only real
     * activity data, without including any test entries that
     * may have been created for testing or development purposes.
     * By applying this scope, developers can easily filter out
     * test records from their activity queries, improving the
     * accuracy and relevance of the results.
     * The 'is_test' attribute is a boolean field in the
     * Activity model that indicates whether a given activity
     * record is a test entry. When this scope is applied,
     * only activities that are not marked as test records
     * will be included in the query results.
     *
     * @param  Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
