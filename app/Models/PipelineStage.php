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
     * Represents an lost stage.
     */
    public const TYPE_LOST = 'lost';

    /**
     * Represents if a stage is won.
     */
    public const IS_WON_STAGE = true;

    /**
     * Represents if a stage is not won.
     */
    public const NOT_WON_STAGE = false;

    /**
     * Represents if a stage is lost.
     */
    public const IS_LOST_STAGE = true;

    /**
     * Represents if a stage is not lost.
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
     * Get the pipeline that owns the stage.
     *
     * @return BelongsTo<Pipeline,PipelineStage>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the deals for the stage.
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
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeWon($query): Builder
    {
        return $query->where('is_won_stage', self::IS_WON_STAGE);
    }

    /**
     * Scope a query to only include lost stages.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeLost($query): Builder
    {
        return $query->where('is_lost_stage', self::IS_LOST_STAGE);
    }

    /**
     * Scope a query to only include open stages.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeOpen($query): Builder
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
     * Get the user that updated the stage.
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
     * Get all of the pipeline stages attachments.
     *
     * @return MorphMany<Attachment
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the pipeline stages activities.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the pipeline stages tasks.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the pipeline stages notes.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the pipeline stage name, applies the test prefix when the pipeline stage is marked as a test.
     *
     * @param  string|null  $value  The raw pipeline stage name from
     * the database.
     *
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
