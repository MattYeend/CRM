<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a sales or workflow pipeline.
 *
 * Pipelines define a structured sequence of stages through which deals
 * progress. They can be marked as default, support soft deletion, and
 * include related activities such as deals, tasks, notes, and attachments.
 *
 * Pipelines may also be flagged as test records, in which case certain
 * attributes (e.g. name) are automatically prefixed.
 *
 * Relationships defined in this model include:
 * - stages(): One-to-many relationship to PipelineStage records that make
 *      up the ordered workflow steps for this pipeline.
 * - deals(): One-to-many relationship to Deal records progressing through
 *      this pipeline.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the pipeline.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the pipeline.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the pipeline.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the pipeline.
 * - creator(): Belongs-to relationship to the User who created the pipeline.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      pipeline.
 * - deleter(): Belongs-to relationship to the User who deleted the pipeline
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the pipeline
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $pipeline = Pipeline::find(1);
 * $stages = $pipeline->stages; // Get all stages for this pipeline
 * $deals = $pipeline->deals; // Get all deals in this pipeline
 * $creator = $pipeline->creator; // Get the user that created the pipeline
 * $updater = $pipeline->updater; // Get the user that last updated the pipeline
 * $deleter = $pipeline->deleter; // Get the user that deleted the pipeline
 * (if applicable)
 * $restorer = $pipeline->restorer; // Get the user that restored the pipeline
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getNameAttribute(): Returns the pipeline name, applying a test prefix
 *      if the pipeline is marked as a test record.
 * - getIsDefaultAttribute(): Returns a boolean indicating whether this is
 *      the default pipeline.
 * - getStageCountAttribute(): Returns the total number of stages in this
 *      pipeline.
 * - getDealCountAttribute(): Returns the total number of deals currently
 *      in this pipeline.
 * Example usage of accessors:
 * ```php
 * $pipeline = Pipeline::find(1);
 * $name = $pipeline->name; // Get the name with test prefix if applicable
 * $isDefault = $pipeline->is_default; // Check if this is the default pipeline
 * $stageCount = $pipeline->stage_count; // Get the number of stages
 * $dealCount = $pipeline->deal_count; // Get the number of active deals
 * ```
 *
 * Query scopes include:
 * - scopeDefault($query): Filter the query to only include the default
 *      pipeline.
 * - scopeWithDeals($query): Filter the query to only include pipelines that
 *      have at least one deal.
 * - scopeWithoutDeals($query): Filter the query to only include pipelines
 *      that have no deals.
 * - scopeReal($query): Filter the query to only include non-test pipelines.
 * Example usage of query scopes:
 * ```php
 * $default = Pipeline::default()->first(); // Get the default pipeline
 * $withDeals = Pipeline::withDeals()->get(); // Pipelines that have deals
 * $withoutDeals = Pipeline::withoutDeals()->get(); // Pipelines with no deals
 * $real = Pipeline::real()->get(); // Exclude test records
 * ```
 */
class Pipeline extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PipelineFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     */
    use HasFactory,
        SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'description',
        'is_default',
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
        'is_default' => 'boolean',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the stages associated with the pipeline.
     *
     * Represents the ordered steps that define the workflow.
     *
     * @return HasMany<PipelineStage>
     */
    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class);
    }

    /**
     * Get the deals associated with the pipeline.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the user that created the pipeline.
     *
     * @return BelongsTo<User,Pipeline>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the pipeline.
     *
     * @return BelongsTo<User,Pipeline>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the pipeline.
     *
     * @return BelongsTo<User,Pipeline>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the pipeline.
     *
     * @return BelongsTo<User,Pipeline>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the pipeline.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the pipeline.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the pipeline.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the pipeline.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the formatted pipeline name.
     *
     * Applies a test prefix when the pipeline is marked as a test record.
     *
     * @param  string|null  $value  The raw pipeline name from the database.
     *
     * @return string The formatted pipeline name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Determine whether this is the default pipeline.
     *
     * The default pipeline is used as the fallback when no specific pipeline
     * is selected during deal creation. Only one pipeline should carry this
     * flag at any given time.
     *
     * @return bool
     */
    public function getIsDefaultAttribute(): bool
    {
        return $this->attributes['is_default'] === true;
    }

    /**
     * Get the total number of stages in this pipeline.
     *
     * Fires a query each time the accessor is called. Avoid using it in
     * loops without eager loading the count via withCount('stages').
     *
     * @return int
     */
    public function getStageCountAttribute(): int
    {
        return $this->stages()->count();
    }

    /**
     * Get the total number of deals currently in this pipeline.
     *
     * Fires a query each time the accessor is called. Avoid using it in
     * loops without eager loading the count via withCount('deals').
     *
     * @return int
     */
    public function getDealCountAttribute(): int
    {
        return $this->deals()->count();
    }

    /**
     * Scope a query to only include the default pipeline.
     *
     * Filters to pipelines where 'is_default' is true. Typically used to
     * retrieve a single record via first() when a fallback pipeline is needed
     * for deal creation or pipeline configuration.
     *
     * @param  Builder<Pipeline> $query The query builder instance.
     *
     * @return Builder<Pipeline> The modified query builder instance.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to only include pipelines that have at least one deal.
     *
     * Uses a whereHas constraint on the deals relationship. Useful for
     * filtering out empty or unused pipelines in reporting and UI views.
     *
     * @param  Builder<Pipeline> $query The query builder instance.
     *
     * @return Builder<Pipeline> The modified query builder instance.
     */
    public function scopeWithDeals(Builder $query): Builder
    {
        return $query->whereHas('deals');
    }

    /**
     * Scope a query to only include pipelines that have no deals.
     *
     * Uses a whereDoesntHave constraint on the deals relationship. Useful
     * for identifying unused or newly created pipelines that have not yet
     * had any deals assigned to them.
     *
     * @param  Builder<Pipeline> $query The query builder instance.
     *
     * @return Builder<Pipeline> The modified query builder instance.
     */
    public function scopeWithoutDeals(Builder $query): Builder
    {
        return $query->whereDoesntHave('deals');
    }

    /**
     * Scope a query to only include non-test pipelines.
     *
     * Filters out any pipeline records where the 'is_test' flag is true,
     * ensuring that queries return only real pipeline records.
     *
     * @param  Builder<Pipeline> $query The query builder instance.
     *
     * @return Builder<Pipeline> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
