<?php

namespace App\Models;

use App\Traits\Lead\HasLeadStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sales lead within the CRM.
 *
 * A lead captures prospective contact information and is
 * optionally owned and assigned to users. Leads can be
 * converted into Deal records via the convertToDeal
 * method once they are ready to progress through the pipeline.
 *
 * Relationships defined in this model include:
 * - owner(): BelongsTo relationship to the User that owns the
 *      lead.
 * - assignedTo(): BelongsTo relationship to the User that is
 *      assigned to the lead.
 * - creator(): BelongsTo relationship to the User that created
 *      the lead.
 * - updater(): BelongsTo relationship to the User that last
 *      updated the lead.
 * - deleter(): BelongsTo relationship to the User that deleted
 *      the lead (if soft-deleted).
 * - restorer(): BelongsTo relationship to the User that restored
 *      the lead (if soft-deleted).
 * - attachments(): MorphMany relationship to Attachment records
 *      associated with the lead.
 * - activities(): MorphMany relationship to Activity records
 *      associated with the lead.
 * - tasks(): MorphMany relationship to Task records associated
 *      with the lead.
 * - notes(): MorphMany relationship to Note records associated
 *      with the lead.
 * Example usage of relationships:
 * ```php
 * $lead = Lead::find(1);
 * $owner = $lead->owner; // Get the owner of the lead
 * $assignedUser = $lead->assignedTo; // Get the user assigned
 *  to the lead
 * $creator = $lead->creator; // Get the user that created the
 *  lead
 * $updater = $lead->updater; // Get the user that last updated
 *  the lead
 * $deleter = $lead->deleter; // Get the user that deleted the
 *  lead (if applicable)
 * $restorer = $lead->restorer; // Get the user that restored
 *  the lead (if applicable)
 * $attachments = $lead->attachments; // Get all attachments
 *  for the lead
 * $activities = $lead->activities; // Get all activities for
 *  the lead
 * $tasks = $lead->tasks; // Get all tasks for the lead
 * $notes = $lead->notes; // Get all notes for the lead
 * ```
 *
 * Accessor methods include:
 * - getTitleAttribute(): Returns the lead title, applying a test
 *      prefix if marked as a test.
 * - getFullNameAttribute(): Returns the full name by concatenating
 *      first and last name.
 * - getDisplayNameAttribute(): Returns the display name, which is
 *      the full name if available, otherwise the email.
 * - getContactInfoAttribute(): Returns a formatted string combining
 *      email and phone number.
 * - getSourceAttribute(): Returns the lead source, applying a
 *      test prefix if marked as a test.
 * - getAgeInDaysAttribute(): Returns the age of the lead in days
 *      since creation.
 * - getIsStaleAttribute(): Returns a boolean indicating whether
 *      the lead is considered stale based on last update time.
 * - getIsHotAttribute(): Returns a boolean indicating whether
 *      the lead is considered hot based on last update time.
 * - getIsEligibleForConversionAttribute(): Returns a boolean
 *      indicating whether the lead is eligible for conversion
 *      based on activity history.
 * - getIsHighPriorityAttribute(): Returns a boolean indicating
 *      whether the lead is considered high priority based on
 *      last update time and contact activity.
 * - getIsLowPriorityAttribute(): Returns a boolean indicating
 *      whether the lead is considered low priority based on
 *      last update time and contact activity.
 * Example usage of accessors:
 * ```php
 * $lead = Lead::find(1);
 * $title = $lead->title; // Get the lead title with test prefix
 *  if applicable
 * $fullName = $lead->full_name; // Get the full name of the lead
 * $displayName = $lead->display_name; // Get the display name of
 *  the lead
 * $contactInfo = $lead->contact_info; // Get the formatted contact
 *  info
 * $source = $lead->source; // Get the lead source with test prefix
 *  if applicable
 * $ageInDays = $lead->age_in_days; // Get the age of the lead in
 *  days
 * $isStale = $lead->is_stale; // Check if the lead is considered
 *  stale
 * $isHot = $lead->is_hot; // Check if the lead is considered hot
 * $isEligibleForConversion = $lead->is_eligible_for_conversion;
 *  // Check if the lead is eligible for conversion
 * $isHighPriority = $lead->is_high_priority; // Check if the lead
 *  is considered high priority
 * $isLowPriority = $lead->is_low_priority; // Check if the lead is
 *  considered low priority
 * ```
 *
 * Query scopes include:
 * - scopeStale($query): Filter the query to only include leads that are
 *      considered stale.
 * - scopeHot($query): Filter the query to only include leads that are
 *      considered hot.
 * - scopeEligibleForConversion($query): Filter the query to only include
 *      leads that are eligible for conversion.
 * - scopeHighPriority($query): Filter the query to only include leads
 *      that are considered high priority.
 * - scopeLowPriority($query): Filter the query to only include leads
 *      that are considered low priority.
 * - scopeConverted($query): Filter the query to only include leads that
 *      have been converted to deals.
 * - scopeUnconverted($query): Filter the query to only include leads
 *      that have not been converted to deals.
 * - scopeContacted($query): Filter the query to only include leads that
 *      have been contacted.
 * - scopeUncontacted($query): Filter the query to only include leads
 *      that have not been contacted.
 * - scopeOwnedBy($query, $userId): Filter the query to only include
 *      leads owned by a specific user.
 * - scopeAssignedTo($query, $userId): Filter the query to only
 *      include leads assigned to a specific user.
 * - scopeFromSource($query, $source): Filter the query to only
 *      include leads from a specific source channel.
 * - scopeReal($query): Filter the query to only include non-test
 *      leads.
 * Example usage of query scopes:
 * ```php
 * $staleLeads = Lead::stale()->get(); // Get leads that are considered
 *  stale
 * $hotLeads = Lead::hot()->get(); // Get leads that are considered
 *  hot
 * $eligibleLeads = Lead::eligibleForConversion()->get(); // Get
 *  leads eligible for conversion
 * $highPriorityLeads = Lead::highPriority()->get(); // Get leads
 *  that are considered high priority
 * $lowPriorityLeads = Lead::lowPriority()->get(); // Get leads
 *  that are considered low priority
 * $convertedLeads = Lead::converted()->get(); // Get leads that
 *  have been converted to deals
 * $unconvertedLeads = Lead::unconverted()->get(); // Get leads
 *  that have not been converted to deals
 * $contactedLeads = Lead::contacted()->get(); // Get leads that
 *  have been contacted
 * $uncontactedLeads = Lead::uncontacted()->get(); // Get leads
 *  that have not been contacted
 * $ownedLeads = Lead::ownedBy($userId)->get(); // Get leads owned
 *  by a specific user
 * $assignedLeads = Lead::assignedTo($userId)->get(); // Get leads
 *  assigned to a specific user
 * $sourceLeads = Lead::fromSource($source)->get(); // Get leads
 *  from a specific source channel
 * $realLeads = Lead::real()->get(); // Get non-test leads
 * ```
 */
class Lead extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LeadFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasLeadStatus<\App\Traits\Lead\HasLeadStatus>
     */
    use HasFactory,
        SoftDeletes,
        HasLeadStatus;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',
        'source',
        'owner_id',
        'assigned_to',
        'assigned_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'is_test',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the owner of the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user assigned to the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Convert the lead to a deal.
     *
     * Creates and persists a new Deal record seeded with the lead's owner
     * and meta data. Pipeline, company, and close date are left unset
     * for the caller to populate as needed.
     *
     * @return Deal The newly created and persisted deal.
     */
    public function convertToDeal(): Deal
    {
        $deal = new Deal([
            'company_id' => null,
            'owner_id' => $this->owner_id,
            'pipeline_id' => null,
            'close_date' => null,
            'status' => Deal::STATUS_OPEN,
            'meta' => $this->meta,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $deal->save();

        return $deal;
    }

    /**
     * Get the user that created the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the lead.
     *
     * @return BelongsTo<User,Lead>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the lead.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the lead.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the lead.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the lead.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the lead title, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw lead title from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the lead's full name by concatenating first and last name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the lead's display name, which is the full name if available,
     * otherwise falls back to the email address.
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name ? $this->full_name : $this->email;
    }

    /**
     * Get the lead's contact information as a formatted string.
     *
     * Combines email and phone number into a single string for display.
     *
     * @return string
     */
    public function getContactInfoAttribute(): string
    {
        $contactInfo = [];

        if ($this->email) {
            $contactInfo[] = "Email: {$this->email}";
        }

        if ($this->phone) {
            $contactInfo[] = "Phone: {$this->phone}";
        }

        return implode(' | ', $contactInfo);
    }

    /**
     * Get the lead source, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw lead source from the database.
     *
     * @return string
     */
    public function getSourceAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the lead's age in days since creation.
     *
     * @return int
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at
            ? $this->created_at->diffInDays(now())
            : 0;
    }

    /**
     * Scope a query to only include leads that are considered stale.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeStale(Builder $query): Builder
    {
        return $query->where('updated_at', '<', now()->subDays(30));
    }

    /**
     * Scope a query to only include leads that are considered hot.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeHot(Builder $query): Builder
    {
        return $query->where('updated_at', '>', now()->subDays(7));
    }

    /**
     * Scope a query to only include leads that are eligible for conversion.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeEligibleForConversion(Builder $query): Builder
    {
        return $query->whereDoesntHave('activities', function ($q) {
            $q->where('type', 'conversion');
        })->whereHas('activities', function ($q) {
            $q->where('type', 'contact');
        });
    }

    /**
     * Scope a query to only include leads that are considered high priority.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('updated_at', '>', now()->subDays(3))
            ->whereDoesntHave('activities', function ($q) {
                $q->where('type', 'contact');
            });
    }

    /**
     * Scope a query to only include leads that are considered low priority.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeLowPriority(Builder $query): Builder
    {
        return $query->where('updated_at', '<', now()->subDays(60))
            ->whereDoesntHave('activities', function ($q) {
                $q->where('type', 'contact');
            });
    }

    /**
     * Scope a query to only include leads that have been converted to deals.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeConverted(Builder $query): Builder
    {
        return $query->whereHas('activities', function ($q) {
            $q->where('type', 'conversion');
        });
    }

    /**
     * Scope a query to only include leads that have not been converted
     * to deals.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeUnconverted(Builder $query): Builder
    {
        return $query->whereDoesntHave('activities', function ($q) {
            $q->where('type', 'conversion');
        });
    }

    /**
     * Scope a query to only include leads that have been contacted.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeContacted(Builder $query): Builder
    {
        return $query->whereHas('activities', function ($q) {
            $q->where('type', 'contact');
        });
    }

    /**
     * Scope a query to only include leads that have not been contacted.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeUncontacted(Builder $query): Builder
    {
        return $query->whereDoesntHave('activities', function ($q) {
            $q->where('type', 'contact');
        });
    }

    /**
     * Scope a query to only include leads that are owned by a specific user.
     *
     * @param  Builder<Lead> $query The query builder instance.
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_id', $userId);
    }

    /**
     * Scope a query to only include leads that are assigned to a specific user.
     *
     * @param  Builder<Lead> $query The query builder instance.
     * @param  int $userId The ID of the user to filter by.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include leads that are sourced from a specific
     * channel.
     *
     * @param  Builder<Lead> $query The query builder instance.
     * @param  string $source The source channel to filter by.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    /**
     * Scope a query to only include non-test leads.
     *
     * @param  Builder<Lead> $query The query builder instance.
     *
     * @return Builder<Lead> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
