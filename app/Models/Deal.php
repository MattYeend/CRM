<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sales deal within the CRM pipeline.
 *
 * A deal is owned by a company and a user, belongs to a pipeline and stage,
 * and tracks its monetary value, currency, close date, and lifecycle status.
 * Products may be attached to a deal via the deal_products pivot table.
 *
 * Relationships defined in this model include:
 * - company(): Belongs-to relationship to the Company that owns the deal.
 * - owner(): Belongs-to relationship to the User who owns the deal.
 * - pipeline(): Belongs-to relationship to the Pipeline the deal is
 *      progressing through.
 * - stage(): Belongs-to relationship to the PipelineStage the deal is
 *      currently in.
 * - products(): Many-to-many relationship to Product records via the
 *      deal_products pivot, including quantity, price, and total.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the deal.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the deal.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the deal.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the deal.
 * - creator(): Belongs-to relationship to the User who created the deal.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      deal.
 * - deleter(): Belongs-to relationship to the User who deleted the deal
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the deal
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $deal = Deal::find(1);
 * $company = $deal->company; // Get the company that owns the deal
 * $owner = $deal->owner; // Get the user who owns the deal
 * $stage = $deal->stage; // Get the current pipeline stage
 * $products = $deal->products; // Get all products with line-item pivot data
 * $tasks = $deal->tasks; // Get all tasks associated with the deal
 * ```
 *
 * Accessor methods include:
 * - getTitleAttribute(): Returns the deal title, applying a test prefix
 *      if the deal is marked as a test record.
 * - getIsOpenAttribute(): Returns a boolean indicating whether the deal
 *      has an open status.
 * - getIsWonAttribute(): Returns a boolean indicating whether the deal
 *      has been won.
 * - getIsLostAttribute(): Returns a boolean indicating whether the deal
 *      has been lost.
 * - getIsClosedAttribute(): Returns a boolean indicating whether the deal
 *      has been closed (either won or lost).
 * - getIsOverdueAttribute(): Returns a boolean indicating whether the deal's
 *      close date has passed without being resolved.
 * - getFormattedValueAttribute(): Returns the deal value formatted to two
 *      decimal places as a string.
 * Example usage of accessors:
 * ```php
 * $deal = Deal::find(1);
 * $title = $deal->title; // Get title with test prefix if applicable
 * $isWon = $deal->is_won; // Check if the deal has been won
 * $isClosed = $deal->is_closed; // Check if won or lost
 * $isOverdue = $deal->is_overdue; // Check if past close date and unresolved
 * $formattedValue = $deal->formatted_value; // e.g. "5000.00"
 * ```
 *
 * Query scopes include:
 * - scopeOpen($query): Filter the query to only include open deals.
 * - scopeWon($query): Filter the query to only include won deals.
 * - scopeLost($query): Filter the query to only include lost deals.
 * - scopeArchived($query): Filter the query to only include archived deals.
 * - scopeClosed($query): Filter the query to only include closed deals
 *      (won or lost).
 * - scopeWithStatus($query, $status): Filter the query to only include
 *      deals with a given status or list of statuses.
 * - scopeOverdue($query): Filter the query to only include deals whose
 *      close date has passed and that are still open.
 * - scopeOwnedBy($query, $userId): Filter the query to only include deals
 *      owned by a given user.
 * - scopeForCompany($query, $companyId): Filter the query to only include
 *      deals belonging to a given company.
 * - scopeForPipeline($query, $pipelineId): Filter the query to only include
 *      deals in a given pipeline.
 * - scopeForStage($query, $stageId): Filter the query to only include deals
 *      in a given pipeline stage.
 * - scopeInCurrency($query, $currency): Filter the query to only include
 *      deals in a given currency or list of currencies.
 * - scopeReal($query): Filter the query to only include non-test deals.
 * Example usage of query scopes:
 * ```php
 * $open = Deal::open()->get(); // Get all open deals
 * $won = Deal::won()->get(); // Get all won deals
 * $overdue = Deal::overdue()->get(); // Open deals past close date
 * $myDeals = Deal::ownedBy($userId)->get(); // Deals for a specific user
 * $pipeline = Deal::forPipeline($pipelineId)->get(); // Deals in a pipeline
 * $real = Deal::real()->get(); // Exclude test records
 * ```
 */
class Deal extends Model
{
    /**
     * @use HasFactory<\Database\Factories\DealFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Represents an open deal still in progress.
     */
    public const STATUS_OPEN = 'open';

    /**
     * Represents a deal that has been successfully closed.
     */
    public const STATUS_WON = 'won';

    /**
     * Represents a deal that was unsuccessful.
     */
    public const STATUS_LOST = 'lost';

    /**
     * Represents a deal that has been archived.
     */
    public const STATUS_ARCHIVED = 'archived';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'company_id',
        'owner_id',
        'pipeline_id',
        'stage_id',
        'value',
        'currency',
        'close_date',
        'status',
        'is_test',
        'meta',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'value' => 'float',
        'close_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the company that owns the deal.
     *
     * @return BelongsTo<Company,Deal>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the owner (user) that owns the deal.
     *
     * @return BelongsTo<User,Deal>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the pipeline that owns the deal.
     *
     * @return BelongsTo<Pipeline,Deal>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the stage that owns the deal.
     *
     * @return BelongsTo<PipelineStage,Deal>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    /**
     * Get the products associated with the deal.
     *
     * Includes pivot data such as quantity, price, and total.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'deal_products')
            ->using(DealProduct::class)
            ->withPivot(['quantity', 'price', 'total'])
            ->withTimestamps();
    }

    /**
     * Get all tasks associated with the deal.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the deal.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get all attachments associated with the deal.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the deal.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get the user that created the deal.
     *
     * @return BelongsTo<User,Deal>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the deal.
     *
     * @return BelongsTo<User,Deal>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the deal.
     *
     * @return BelongsTo<User,Deal>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the deal.
     *
     * @return BelongsTo<User,Deal>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the deal title, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw title from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Determine whether the deal has an open status.
     *
     * @return bool
     */
    public function getIsOpenAttribute(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /**
     * Determine whether the deal has been won.
     *
     * @return bool
     */
    public function getIsWonAttribute(): bool
    {
        return $this->status === self::STATUS_WON;
    }

    /**
     * Determine whether the deal has been lost.
     *
     * @return bool
     */
    public function getIsLostAttribute(): bool
    {
        return $this->status === self::STATUS_LOST;
    }

    /**
     * Determine whether the deal has been closed.
     *
     * A deal is considered closed when its status is either won or lost.
     * Archived deals are not included in this check as they represent a
     * separate lifecycle state.
     *
     * @return bool
     */
    public function getIsClosedAttribute(): bool
    {
        return in_array($this->status, [
            self::STATUS_WON,
            self::STATUS_LOST,
        ], true);
    }

    /**
     * Determine whether the deal is overdue.
     *
     * A deal is overdue when its close date has passed and it has not yet
     * been won, lost, or archived. Useful for pipeline health reports and
     * automated follow-up prompts.
     *
     * @return bool
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->close_date !== null
            && $this->close_date->isPast()
            && $this->status === self::STATUS_OPEN;
    }

    /**
     * Get the deal value formatted to two decimal places.
     *
     * Returns the value as a string without currency symbols. Pair with
     * the deal's currency field for a fully formatted display value.
     *
     * @return string
     */
    public function getFormattedValueAttribute(): string
    {
        return number_format((float) $this->value, 2, '.', '');
    }

    /**
     * Scope a query to only include open deals.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include won deals.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeWon(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WON);
    }

    /**
     * Scope a query to only include lost deals.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LOST);
    }

    /**
     * Scope a query to only include archived deals.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    /**
     * Scope a query to only include closed deals.
     *
     * A deal is considered closed when its status is either won or lost.
     * Useful for win/loss reporting and closed pipeline analysis.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_WON,
            self::STATUS_LOST,
        ]);
    }

    /**
     * Scope a query to only include deals with a given status or list of
     * statuses.
     *
     * Accepts either a single status string or an array of status values.
     * Values should match one of the STATUS_* constants defined on this model.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  string|array<int,string> $status The status or statuses to
     * filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeWithStatus(
        Builder $query,
        string|array $status
    ): Builder {
        return is_array($status)
            ? $query->whereIn('status', $status)
            : $query->where('status', $status);
    }

    /**
     * Scope a query to only include overdue deals.
     *
     * A deal is overdue when its close date is in the past and its status
     * is still open. Useful for pipeline health monitoring and prompting
     * sales team follow-up actions.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('close_date')
            ->where('close_date', '<', now())
            ->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope a query to only include deals owned by a given user.
     *
     * Filters by the 'owner_id' column. Useful for building personal deal
     * views, user performance reports, and task assignment lookups.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  int $userId The ID of the owning user to filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('owner_id', $userId);
    }

    /**
     * Scope a query to only include deals belonging to a given company.
     *
     * Filters by the 'company_id' column. Useful for loading all deals
     * for a specific company without going through the Company model's
     * relationship.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  int $companyId The ID of the company to filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include deals in a given pipeline.
     *
     * Filters by the 'pipeline_id' column. Useful for building pipeline
     * board views or generating pipeline-specific reports.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  int $pipelineId The ID of the pipeline to filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeForPipeline(Builder $query, int $pipelineId): Builder
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    /**
     * Scope a query to only include deals in a given pipeline stage.
     *
     * Filters by the 'stage_id' column. Useful for stage-specific deal
     * counts or for loading deals that need to be progressed to the next
     * step in a workflow.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  int $stageId The ID of the pipeline stage to filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeForStage(Builder $query, int $stageId): Builder
    {
        return $query->where('stage_id', $stageId);
    }

    /**
     * Scope a query to only include deals in a given currency or list of
     * currencies.
     *
     * Accepts either a single currency code as a string or an array of
     * currency codes. Applies a where or whereIn clause accordingly.
     *
     * @param  Builder<Deal> $query The query builder instance.
     * @param  string|array<int,string> $currency The currency code
     * or codes to filter by.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeInCurrency(
        Builder $query,
        string|array $currency
    ): Builder {
        return is_array($currency)
            ? $query->whereIn('currency', $currency)
            : $query->where('currency', $currency);
    }

    /**
     * Scope a query to only include non-test deals.
     *
     * Filters out any deal records where the 'is_test' flag is true, ensuring
     * that queries return only real deal records. Important for accurate
     * pipeline reporting and revenue forecasting.
     *
     * @param  Builder<Deal> $query The query builder instance.
     *
     * @return Builder<Deal> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
