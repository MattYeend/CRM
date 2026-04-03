<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Represents an image associated with a part.
 *
 * Each part may have multiple images, but only one may be designated as
 * the primary image. The booted method enforces this constraint by
 * automatically demoting any existing primary image when a new one is saved
 * with is_primary set to true.
 *
 * Relationships defined in this model include:
 * - part(): Belongs-to relationship to the Part this image belongs to.
 * - creator(): The user that created the image.
 * - updater(): The user that last updated the image.
 * - deleter(): The user that deleted the image (if soft-deleted).
 * - restorer(): The user that restored the image (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $image = PartImage::find(1);
 * $part = $image->part; // Get the associated part
 * $creator = $image->creator; // Get the user that created the image
 * $updater = $image->updater; // Get the user that last updated the image
 * $deleter = $image->deleter; // Get the user that deleted the image
 * (if applicable)
 * $restorer = $image->restorer; // Get the user that restored the image
 * (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getImageUrlAttribute(): Returns the full URL of the image using Laravel's
 *      Storage facade.
 * - getThumbnailUrlAttribute(): Returns the full URL of the thumbnail version
 *      of the image, assuming a 'thumbnails' subdirectory.
 * - getThumbnailOrImageUrlAttribute(): Returns the thumbnail URL if
 *      available, or falls back to the main image URL.
 * Example usage of accessors:
 * ```php
 * $image = PartImage::find(1);
 * $imageUrl = $image->image_url; // Get the full URL of the image
 * $thumbnailUrl = $image->thumbnail_url; // Get the full URL of the thumbnail
 * $thumbnailOrImageUrl = $image->thumbnail_or_image_url; // Get the thumbnail
 * URL or fallback to the main image URL
 * ```
 *
 * Helper methods include:
 * - getIsPrimary(): Returns true if this image is marked as the primary image
 *      for its part.
 * - getIsTestImage(): Returns true if this image is marked as a test image.
 * Example usage of helper methods:
 * ```php
 * $image = PartImage::find(1);
 * if ($image->is_primary) {
 *   // This is the primary image for the part
 * }
 * if ($image->is_test_image) {
 *  // This image is marked as a test image
 * }
 * ```
 * * Query scopes include:
 * - scopePrimary($query): Filter the query to only include primary images.
 * - scopeReal($query): Filter the query to only include non-test images.
 * Example usage of query scopes:
 *  ```php
 * $primaryImages = PartImage::primary()->get(); // Get all primary images
 * $realImages = PartImage::real()->get(); // Get all non-test images
 * ```
 */
class PartImage extends Model
{
    /**
     * @use HasFactory<\Database\Factories\PartImageFactory>
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
        'part_id',
        'image',
        'alt',
        'is_primary',
        'sort_order',
        'is_test',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the part this image belongs to.
     *
     * @return BelongsTo<Part,PartImage>
     */
    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    /**
     * Get the URL of the image.
     *
     * This accessor assumes that the 'image' attribute stores a
     * path relative to the storage disk. It generates a full
     * URL using Laravel's Storage facade.
     *
     * @return string|null
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return null;
    }

    /**
     * Get the URL of the thumbnail version of the image.
     *
     * This accessor assumes that thumbnail images are stored in a
     * 'thumbnails' subdirectory with the same filename.
     * It generates a full URL using Laravel's Storage facade.
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($this->image) {
            $thumbnailPath = 'thumbnails/' . basename($this->image);
            return Storage::url($thumbnailPath);
        }
        return null;
    }

    /**
     * Get the URL of the thumbnail image, or the main image if the
     * thumbnail is not available.
     *
     * This accessor provides a fallback mechanism to ensure that an
     * image URL is always available, even if the thumbnail version
     * is missing.
     *
     * @return string|null
     */
    public function getThumbnailOrImageUrlAttribute(): ?string
    {
        $thumbnailUrl = $this->thumbnail_url;
        if ($thumbnailUrl) {
            return $thumbnailUrl;
        }
        return $this->image_url;
    }

    /**
     * Get the user that created the image.
     *
     * @return BelongsTo<User,PartImage>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the image.
     *
     * @return BelongsTo<User,PartImage>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the image.
     *
     * @return BelongsTo<User,PartImage>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the image.
     *
     * @return BelongsTo<User,PartImage>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Determine if this image is the primary image for its part.
     *
     * This method provides a convenient way to check if the image
     * is marked as primary.
     * It can be used in views or other parts of the application to
     * conditionally display or highlight the primary image.
     *
     * @return bool
     */
    public function getIsPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Determine if this image is a test image.
     *
     * This method provides a convenient way to check if the image is
     * marked as a test image.
     * Test images may be used for development or staging purposes and
     * should not be displayed in production environments.
     *
     * @return bool
     */
    public function getIsTestImage(): bool
    {
        return $this->is_test;
    }

    /**
     * Scope a query to only include primary images.
     *
     * This scope filters the query to include only images where the
     * 'is_primary' attribute is true. This is useful for retrieving
     * the primary image for a part, especially when there may be
     * multiple images associated with that part.
     *
     * @param  Builder<PartImage> $query The query builder instance.
     *
     * @return Builder<PartImage> The modified query builder instance.
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include non-test images.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring that
     * only real production data is included in the results.
     *
     * @param  Builder<PartImage> $query The query builder instance.
     *
     * @return Builder<PartImage> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Bootstrap the model and its traits.
     *
     * Registers a saving listener that ensures only one image per part is
     * marked as primary. When a new image is saved with is_primary set to
     * true, all other images for the same part are demoted.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::saving(function (PartImage $image) {
            if ($image->is_primary) {
                static::where('part_id', $image->part_id)
                    ->where('id', '!=', $image->id ?? 0)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
