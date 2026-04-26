<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Represents a hierarchical category for organising parts.
 *
 * Categories may be nested via a self-referential parent/children
 * relationship. A URL-friendly slug is automatically generated from the
 * category name on creation and regenerated whenever the name changes.
 *
 * Relationships defined in this model include:
 * - parent(): The parent category of this category
 *      (if any).
 * - children(): The child categories belonging to
 *      this category.
 * - parts(): The parts belonging to this category.
 * - creator(): The user that created the category.
 * - updater(): The user that last updated the category.
 * - deleter(): The user that deleted the category
 *      (if soft-deleted).
 * - restorer(): The user that restored the category
 *      (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $category = PartCategory::find(1);
 * $parent = $category->parent; // Get the parent category
 * $children = $category->children; // Get the child categories
 * $parts = $category->parts; // Get the parts in this category
 * $creator = $category->creator; // Get the user that created
 *  this category
 * $updater = $category->updater; // Get the user that last updated
 *  this category
 * $deleter = $category->deleter; // Get the user that deleted
 *  this category (if applicable)
 * $restorer = $category->restorer; // Get the user that restored
 * this category (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getSlugAttribute(): Returns the URL-friendly slug for the category,
 *      generated from the name.
 * - getFullPathAttribute(): Returns the full hierarchical path of the
 *      category as a string, showing its position in the hierarchy.
 * Example usage of accessors:
 * ```php
 * $category = PartCategory::find(1);
 * $slug = $category->slug; // Get the slug for this category
 * $fullPath = $category->full_path; // Get the full hierarchical
 * path for this category
 * ```
 *
 *  Utility methods include:
 * - ancestors(): Returns a collection of all parent categories, ordered
 *      from the top-most parent down to the immediate parent of this
 *      category. Useful for building full paths or breadcrumbs.
 * Example usage:
 * ```php
 * $category = PartCategory::find(5);
 * $ancestors = $category->ancestors(); // Collection of parent categories
 * foreach ($ancestors as $ancestor) {
 *     echo $ancestor->name . ' > ';
 * }
 * ```
 *
 * Query scopes include:
 * - scopeWithName($query, $name): Filter categories by a specific
 *      name.
 * - scopeReal($query): Filter the query to only include non-test
 *      categories.
 * Example usage of query scopes:
 * ```php
 * $categoriesWithName = PartCategory::withName('Screws')->get();
 * // Get categories with the name "Screws"
 * $realCategories = PartCategory::real()->get(); // Get all non-test
 * categories
 * ```
 */
class PartCategory extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartCategoryFactory>
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
     * Get the URL-friendly slug for the category.
     *
     * The slug is automatically generated from the category
     * name on creation and regenerated whenever the name
     * changes. It is used for clean URLs and should be
     * unique across categories to avoid conflicts.
     *
     * @return string The slug for the category.
     */
    public function getSlugAttribute(): string
    {
        return Str::slug($this->name);
    }

    /**
     * Get the full hierarchical path of the category as a string.
     *
     * This accessor builds a string representation of the category's position
     * in the hierarchy by concatenating the names of its ancestors and itself,
     * separated by " > ". For example, a category "Screws" under "Fasteners"
     * would return "Fasteners > Screws".
     * This is useful for displaying the category in a user-friendly format
     * that shows its context within the hierarchy.
     *
     * @return string The full hierarchical path of the category.
     */
    public function getFullPathAttribute(): string
    {
        $ancestors = $this->ancestors()->pluck('name')->toArray();
        return implode(' > ', array_merge($ancestors, [$this->name]));
    }

    /**
     * Get all ancestor categories of this category as a Collection.
     *
     * This method walks up the parent hierarchy, collecting each parent
     * category until it reaches a top-level category with no parent.
     * The returned collection is ordered from the top-most ancestor
     * down to the immediate parent of this category.
     *
     * @return Collection<PartCategory>
     */
    public function ancestors()
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent;
            $parent = $parent->parent;
        }

        return collect(array_reverse($ancestors));
    }

    /**
     * Scope a query to get part categories with a name matching the given
     * value.
     *
     * @param  Builder<PartCategory> $query The query builder instance.
     * @param  string $name The name to filter by.
     *
     * @return Builder<PartCategory> The modified query builder instance.
     */
    public function scopeWithName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Scope a query to only include non-test categories.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring that
     * only real production data is included in the results.
     *
     * @param  Builder<PartCategory> $query The query builder instance.
     *
     * @return Builder<PartCategory> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Bootstrap the model and its traits.
     *
     * Registers model event listeners to automatically
     * generate a URL-friendly slug from the category
     * name on creation, and regenerate it if the name
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
