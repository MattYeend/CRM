<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillOfMaterial extends Model
{
    /**
     * @use HasFactory<\Database\Factories\BillOfMaterialFactory>
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
        'parent_part_id',
        'child_part_id',
        'quantity',
        'unit_of_measure',
        'notes',
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
        'meta' => 'array',
        'is_test' => 'boolean',
        'quantity' => 'decimal:4',
    ];

    /**
     * Get the parent part that this BOM entry belongs to.
     * The parent is the manufactured part that requires child components.
     *
     * @return BelongsTo<Part,BillOfMaterial>
     */
    public function parentPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'parent_part_id');
    }

    /**
     * Get the child part (component) consumed by the parent part.
     *
     * @return BelongsTo<Part,BillOfMaterial>
     */
    public function childPart(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'child_part_id');
    }

    /**
     * Get the user that created the bom.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the bom.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the bom.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the bom.
     *
     * @return BelongsTo<User,BillOfMaterial>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }
}
