<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a single stage within a pipeline.
 *
 * Pipeline stages define the ordered steps that deals progress through.
 * Each stage can represent an open, won, or lost state, and includes
 * positional ordering within the pipeline.
 *
 * Stages support lifecycle tracking, related entities (deals, tasks,
 * notes, attachments), and may be marked as test records with prefixed names.
 *
 * Relationships defined in this model include:
 * - pipeline(): Belongs-to relationship to the Pipeline that owns this stage.
 * - deals(): One-to-many relationship to Deal records currently assigned
 *      to this stage.
 * - attachments(): Polymorphic one-to-many relationship to Attachment
 *      records associated with the stage.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the stage.
 * - tasks(): Polymorphic one-to-many relationship to Task records
 *      associated with the stage.
 * - notes(): Polymorphic one-to-many relationship to Note records
 *      associated with the stage.
 * - creator(): Belongs-to relationship to the User who created the stage.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      stage.
 * - deleter(): Belongs-to relationship to the User who deleted the stage
 *      (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the stage
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $stage = PipelineStage::find(1);
 * $pipeline = $stage->pipeline; // Get the parent pipeline
 * $deals = $stage->deals; // Get all deals in this stage
 * $notes = $stage->notes; // Get all notes associated with this stage
 * $creator = $stage->creator; // Get the user that created this stage
 * $updater = $stage->updater; // Get the user that last updated this stage
 * $deleter = $stage->deleter; // Get the user that deleted this stage
 * (if applicable)
 * $restorer = $stage->restorer; // Get the user that restored this stage
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getNameAttribute(): Returns the stage name, applying a test prefix
 *      if the stage is marked as a test record.
 * - getIsOpenAttribute(): Returns a boolean indicating whether the stage
 *      is an open (in-progress) stage, i.e. neither won nor lost.
 * - getIsWonAttribute(): Returns a boolean indicating whether the stage
 *      is a won stage.
 * - getIsLostAttribute(): Returns a boolean indicating whether the stage
 *      is a lost stage.
 * - getDealCountAttribute(): Returns the total number of deals currently
 *      assigned to this stage.
 * Example usage of accessors:
 * ```php
 * $stage = PipelineStage::find(1);
 * $name = $stage->name; // Get the name with test prefix if applicable
 * $isOpen = $stage->is_open; // Check if the stage is still in progress
 * $isWon = $stage->is_won; // Check if this is a won stage
 * $dealCount = $stage->deal_count; // Get the number of deals in this stage
 * ```
 *
 * Query scopes include:
 * - scopeWon($query): Filter the query to only include won stages.
 * - scopeLost($query): Filter the query to only include lost stages.
 * - scopeOpen($query): Filter the query to only include open (in-progress)
 *      stages that are neither won nor lost.
 * - scopeForPipeline($query, $pipelineId): Filter the query to only include
 *      stages belonging to a given pipeline.
 * - scopeOrdered($query): Order stages by their position column ascending.
 * - scopeReal($query): Filter the query to only include non-test stages.
 * Example usage of query scopes:
 * ```php
 * $openStages = PipelineStage::open()->get(); // Get open stages
 * $wonStages = PipelineStage::won()->get(); // Get won stages
 * $ordered = PipelineStage::forPipeline($id)->ordered()->get();
 *  // Ordered stages
 * $real = PipelineStage::real()->get(); // Exclude test records
 * ```
 */
class PipelineStage extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PipelineStageFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * Represents an open stage still in progress.
     */
    public const TYPE_OPEN = 'open';

    /**
     * Represents a won stage.
     */
    public const TYPE_WON = 'won';

    /**
     * Represents a lost stage.
     */
    public const TYPE_LOST = 'lost';

    /**
     * Indicates the stage is a winning stage.
     */
    public const IS_WON_STAGE = true;

    /**
     * Indicates the stage is not a winning stage.
     */
    public const NOT_WON_STAGE = false;

    /**
     * Indicates the stage is a lost stage.
     */
    public const IS_LOST_STAGE = true;

    /**
     * Indicates the stage is not a lost stage.
     */
    public const NOT_LOST_STAGE = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'pipeline_id',
        'deal_id',
        'name',
        'position',
        'is_won_stage',
        'is_lost_stage',
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
        'is_won_stage' => 'boolean',
        'is_lost_stage' => 'boolean',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the pipeline that owns this stage.
     *
     * @return BelongsTo<Pipeline,PipelineStage>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the deals currently assigned to this stage.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }

    /**
     * Get the user that created the stage.
     *
     * @return BelongsTo<User,PipelineStage>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the stage.
     *
     * @return BelongsTo<User,PipelineStage>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the stage.
     *
     * @return BelongsTo<User,PipelineStage>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the stage.
     *
     * @return BelongsTo<User,PipelineStage>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all attachments associated with the stage.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the stage.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the stage.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the stage.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the formatted stage name.
     *
     * Applies a test prefix when the stage is marked as a test record.
     *
     * @param  string|null  $value  The raw stage name from the database.
     *
     * @return string The formatted stage name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Determine whether this is an open (in-progress) stage.
     *
     * A stage is considered open when it is neither a won stage nor a lost
     * stage. Open stages represent active deal progress and are the primary
     * stages used in pipeline board views.
     *
     * @return bool
     */
    public function getIsOpenAttribute(): bool
    {
        return ! $this->is_won_stage && ! $this->is_lost_stage;
    }

    /**
     * Determine whether this is a won stage.
     *
     * Won stages mark the successful completion of a deal. Deals that reach
     * a won stage are typically excluded from active pipeline reporting.
     *
     * @return bool
     */
    public function getIsWonAttribute(): bool
    {
        return $this->is_won_stage === true;
    }

    /**
     * Determine whether this is a lost stage.
     *
     * Lost stages mark the unsuccessful close of a deal. Deals that reach
     * a lost stage are typically excluded from active pipeline reporting.
     *
     * @return bool
     */
    public function getIsLostAttribute(): bool
    {
        return $this->is_lost_stage === true;
    }

    /**
     * Get the total number of deals currently assigned to this stage.
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
     * Scope a query to only include won stages.
     *
     * @param  Builder<PipelineStage> $query The query builder instance.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeWon(Builder $query): Builder
    {
        return $query->where('is_won_stage', self::IS_WON_STAGE);
    }

    /**
     * Scope a query to only include lost stages.
     *
     * @param  Builder<PipelineStage> $query The query builder instance.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeLost(Builder $query): Builder
    {
        return $query->where('is_lost_stage', self::IS_LOST_STAGE);
    }

    /**
     * Scope a query to only include open (active) stages.
     *
     * Open stages are those that are neither won nor lost, representing
     * the in-progress steps of a pipeline workflow.
     *
     * @param  Builder<PipelineStage> $query The query builder instance.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_won_stage', self::NOT_WON_STAGE)
            ->where('is_lost_stage', self::NOT_LOST_STAGE);
    }

    /**
     * Scope a query to only include stages belonging to a given pipeline.
     *
     * Filters by the 'pipeline_id' column. Useful for loading the stages
     * of a specific pipeline without going through the Pipeline model's
     * relationship, for example when building a pipeline board view.
     *
     * @param  Builder<PipelineStage>  $query The query builder instance.
     * @param  int $pipelineId The ID of the pipeline to filter by.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeForPipeline(Builder $query, int $pipelineId): Builder
    {
        return $query->where('pipeline_id', $pipelineId);
    }

    /**
     * Scope a query to order stages by their position ascending.
     *
     * Ensures that stages are returned in the correct display order for
     * pipeline board views and stage selection dropdowns.
     *
     * @param  Builder<PipelineStage> $query The query builder instance.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    /**
     * Scope a query to only include non-test stages.
     *
     * Filters out any stage records where the 'is_test' flag is true,
     * ensuring that queries return only real pipeline stages.
     *
     * @param  Builder<PipelineStage> $query The query builder instance.
     *
     * @return Builder<PipelineStage> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }
}
