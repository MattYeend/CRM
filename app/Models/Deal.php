<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
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
     * Get the products associated with the deal.
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
}
