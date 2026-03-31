<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Represents a hierarchical category for organising parts.
 *
 * Categories may be nested via a self-referential parent/children
 * relationship. A URL-friendly slug is automatically generated from the
 * category name on creation and regenerated whenever the name changes.
 */
class PartCategory extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartCategoryFactory>
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
        'parent_id',
        'name',
        'description',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the parent category of this category.
     *
     * @return BelongsTo<PartCategory,PartCategory>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'parent_id');
    }

    /**
     * Get the child categories belonging to this category.
     *
     * @return HasMany<PartCategory>
     */
    public function children(): HasMany
    {
        return $this->hasMany(PartCategory::class, 'parent_id');
    }

    /**
     * Get the parts belonging to this category.
     *
     * @return HasMany<Part>
     */
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'category_id');
    }

    /**
     * Get the user that created the part category.
     *
     * @return BelongsTo<User,PartCategory>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the part category.
     *
     * @return BelongsTo<User,PartCategory>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the part category.
     *
     * @return BelongsTo<User,PartCategory>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the part category.
     *
     * @return BelongsTo<User,PartCategory>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Bootstrap the model and its traits.
     *
     * Registers model event listeners to automatically generate a URL-friendly
     * slug from the category name on creation, and regenerate it if the name
     * is changed during an update.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PartCategory $partCategory) {
            $partCategory->slug = Str::slug($partCategory->name);
        });

        static::updating(function (PartCategory $partCategory) {
            if ($partCategory->isDirty('name')) {
                $partCategory->slug = Str::slug($partCategory->name);
            }
        });
    }
}
