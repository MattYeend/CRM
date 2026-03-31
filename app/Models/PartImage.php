<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents an image associated with a part.
 *
 * Each part may have multiple images, but only one may be designated as
 * the primary image. The booted method enforces this constraint by
 * automatically demoting any existing primary image when a new one is saved
 * with is_primary set to true.
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
