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
     * Open stages are those that are neither won nor lost.
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
     * @param  string|null $value The raw stage name from the database.
     *
     * @return string The formatted stage name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
