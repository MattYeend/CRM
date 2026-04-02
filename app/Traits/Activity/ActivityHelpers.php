<?php

namespace App\Traits\Activity;

/**
 * Defines helper methods for the Activity model.
 *
 * This trait encapsulates common accessors and utility methods related
 * to the Activity model, such as retrieving the activity type with
 * test prefixing, getting the name of the assigned user, and checking
 * the subject type of the activity.
 * By using this trait within the Activity model,
 * we maintain a clean separation of concerns, keeping helper methods organized
 * and easily maintainable.
 * The methods defined here provide convenient access to frequently used
 * attributes and checks,
 * enhancing the readability and usability of the Activity model
 * throughout the application.
 *
 * Methods defined in this trait include:
 * - getTypeAttribute($value): string - Accessor for the activity
 *      type that applies test prefixing.
 * - getUserNameAttribute(): ?string - Accessor for the name of
 *      the assigned user, returning null if no user is associated.
 * - hasSubjectType($type): bool - Utility method to check if the
 *      activity's subject type matches a given type.
 * Note: The actual implementation of these methods is provided in
 * the trait, and they are intended to be used as part of the Activity
 * model's functionality.
 * This trait should be used in conjunction with the ActivityRelationships
 * and ActivityScopes traits to provide a complete set of features
 * for the Activity model.
 * The Activity model itself should also use the HasTestPrefix trait
 * to enable test prefixing functionality for the activity type.
 * The methods defined here can be used in Eloquent queries and model
 * interactions to easily access and check activity attributes,
 * improving the efficiency and clarity of code that interacts with
 * activities and their associated models.
 * Example usage of these methods in code might look like:
 * ```php
 * $activity = Activity::find(1);
 * $type = $activity->type; // Get the activity type with
 * test prefixing applied
 * $userName = $activity->user_name; // Get the name of the
 * assigned user, or null if no user is associated
 * $isCompanyActivity = $activity->hasSubjectType(Company::class);
 * // Check if the activity's subject type is Company
 * $isDealActivity = $activity->hasSubjectType(Deal::class); // Check if
 * the activity's subject type is Deal
 * ```
 */
trait ActivityHelpers
{
    /**
     * Get the activity type, applying the test prefix when marked as a test.
     *
     * @param  string|null $value The raw type value from the database.
     *
     * @return string
     */
    public function getTypeAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the name of the user that owns the activity.
     *
     * Returns null if no user is associated.
     *
     * @return string|null
     */
    public function getUserNameAttribute(): ?string
    {
        return $this->user?->name;
    }

    /**
     * Determine whether the activity subject is of a given type.
     *
     * @param  string $type A fully-qualified class name (e.g. Company::class).
     *
     * @return bool
     */
    public function hasSubjectType(string $type): bool
    {
        return $this->subject_type === $type;
    }
}
