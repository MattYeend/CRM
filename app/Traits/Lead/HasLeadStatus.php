<?php

namespace App\Traits\Lead;

/**
 * Trait HasLeadStatus
 *
 * This trait provides methods to determine the status of a lead based on its activities and update timestamps. It includes methods to check if a lead is stale, hot, contacted, converted, eligible for conversion, high priority, or low priority. The status is determined based on the presence of specific activity types and the timing of the last update.
 * Example usage:
 * ```php
 * $lead = Lead::find(1);
 * if ($lead->is_stale) {
 *      // Handle stale lead
 * }
 * if ($lead->is_hot) {
 *      // Handle hot lead
 * }
 * if ($lead->is_contacted) {
 *      // Handle contacted lead
 * }
 * if ($lead->is_converted) {
 *      // Handle converted lead
 * }
 * if ($lead->is_eligible_for_conversion) {
 *      // Handle lead eligible for conversion
 * }
 * if ($lead->is_high_priority) {
 *      // Handle high priority lead
 * }
 * if ($lead->is_low_priority) {
 *      // Handle low priority lead
 * }
 */
trait HasLeadStatus
{
    /**
     * Check if the lead has an activity of the specified type.
     *
     * @param string $type The type of activity to check for (e.g., 'contact', 'conversion').
     *
     * @return bool Returns true if the lead has an activity of the specified type, otherwise false.
     */
    protected function hasActivity(string $type): bool
    {
        return $this->activities()->where('type', $type)->exists();
    }

    /**
     * Check if the lead was updated before a certain number of days ago.
     *
     * @param int $days The number of days to check against.
     *
     * @return bool Returns true if the lead was updated before the specified number of days ago, otherwise false.
     */
    protected function updatedBeforeDays(int $days): bool
    {
        return $this->updated_at?->lt(now()->subDays($days)) ?? false;
    }

    /**
     * Check if the lead was updated within a certain number of days.
     *
     * @param int $days The number of days to check against.
     *
     * @return bool Returns true if the lead was updated within the specified number of days, otherwise false.
     */
    protected function updatedWithinDays(int $days): bool
    {
        return $this->updated_at?->gt(now()->subDays($days)) ?? false;
    }

    /**
     * Determine if the lead is considered "stale" based on its last update time.
     *
     * A lead is considered stale if it has not been updated in the last 30 days.
     *
     * @return bool Returns true if the lead is stale, otherwise false.
     */
    public function getIsStaleAttribute(): bool
    {
        return $this->updatedBeforeDays(30);
    }

    /**
     * Determine if the lead is considered "hot" based on its last update time.
     *
     * A lead is considered hot if it has been updated within the last 7 days.
     *
     * @return bool Returns true if the lead is hot, otherwise false.
     */
    public function getIsHotAttribute(): bool
    {
        return $this->updatedWithinDays(7);
    }

    /**
     * Determine if the lead has been contacted based on its activities.
     *
     * A lead is considered contacted if it has at least one activity of type 'contact'.
     *
     * @return bool Returns true if the lead has been contacted, otherwise false.
     */
    public function getIsContactedAttribute(): bool
    {
        return $this->hasActivity('contact');
    }

    /**
     * Determine if the lead has been converted based on its activities.
     *
     * A lead is considered converted if it has at least one activity of type 'conversion'.
     *
     * @return bool Returns true if the lead has been converted, otherwise false.
     */
    public function getIsConvertedAttribute(): bool
    {
        return $this->hasActivity('conversion');
    }

    /**
     * Determine if the lead is eligible for conversion.
     *
     * A lead is eligible for conversion if it has been contacted but not yet converted.
     *
     * @return bool Returns true if the lead is eligible for conversion, otherwise false.
     */
    public function getIsEligibleForConversionAttribute(): bool
    {
        return ! $this->is_converted && $this->is_contacted;
    }

    /**
     * Determine if the lead is considered high priority.
     *
     * A lead is considered high priority if it has been updated within the last 3 days and has not been contacted.
     *
     * @return bool Returns true if the lead is high priority, otherwise false.
     */
    public function getIsHighPriorityAttribute(): bool
    {
        return $this->updatedWithinDays(3) && ! $this->is_contacted;
    }

    /**
     * Determine if the lead is considered low priority.
     *
     * A lead is considered low priority if it has not been updated in the last 60 days and has not been contacted.
     *
     * @return bool Returns true if the lead is low priority, otherwise false.
     */
    public function getIsLowPriorityAttribute(): bool
    {
        return $this->updatedBeforeDays(60) && ! $this->is_contacted;
    }
}
