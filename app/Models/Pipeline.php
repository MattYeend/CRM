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
 * Represents a sales or workflow pipeline.
 *
 * Pipelines define a structured sequence of stages through which deals
 * progress. They can be marked as default, support soft deletion, and
 * include related activities such as deals, tasks, notes, and attachments.
 *
 * Pipelines may also be flagged as test records, in which case certain
 * attributes (e.g. name) are automatically prefixed.
 */
class Pipeline extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PipelineFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

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
     * Scope a query to only include the default pipeline.
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
     * @param  string|null $value The raw pipeline name from the database.
     *
     * @return string The formatted pipeline name.
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
