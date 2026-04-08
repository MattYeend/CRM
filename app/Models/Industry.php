<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Represents an industry used to classify companies within the CRM.
 *
 * A URL-friendly slug is automatically generated from the industry name
 * on creation and regenerated whenever the name changes.
 *
 * Relationships defined in this model include:
 * - companies(): The companies belonging to this industry.
 * - creator(): The user that created the industry.
 * - updater(): The user that last updated the industry.
 * - deleter(): The user that deleted the industry
 *      (if soft-deleted).
 * - restorer(): The user that restored the industry
 *      (if soft-deleted).
 *
 * Example usage of relationships:
 * ```php
 * $industry = Industry::find(1);
 * $companies = $industry->companies; // Get the companies in this industry
 * $creator = $industry->creator;     // Get the user that created this industry
 * $updater = $industry->updater;     // Get the user that last updated this industry
 * $deleter = $industry->deleter;     // Get the user that deleted this industry
 *                                    //   (if applicable)
 * $restorer = $industry->restorer;   // Get the user that restored this industry
 *                                    //   (if applicable)
 * ```
 */
class Industry extends Model
{
    /** 
     * @use HasFactory<\Database\Factories\IndustryFactory> 
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
        'slug',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'restored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the comany for the industry.
     *
     * @return HasMany<Company>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Get the user that created the industry.
     *
     * @return BelongsTo<User,Industry>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the industry.
     *
     * @return BelongsTo<User,Industry>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the industry.
     *
     * @return BelongsTo<User,Industry>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the industry.
     *
     * @return BelongsTo<User,Industry>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
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

        static::creating(function (Industry $industry) {
            $industry->slug = Str::slug($industry->name);
        });

        static::updating(function (Industry $industry) {
            if ($industry->isDirty('name')) {
                $industry->slug = Str::slug($industry->name);
            }
        });
    }
}
