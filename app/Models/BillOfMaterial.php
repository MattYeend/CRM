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
        'scrap_percentage',
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
        'scrap_percentage' => 'decimal:2',
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

    /**
     * Quantity including scrap
     *
     * @return float
     */
    public function effectiveQuantity(): float
    {
        $scrap = (float) ($this->scrap_percentage ?? 0);

        return (float) $this->quantity * (1 + ($scrap / 100));
    }

    /**
     * Direct cost (no recursion)
     *
     * @return float|null
     */
    public function lineCost(): ?float
    {
        $cost = $this->childPart?->cost_price;

        if ($cost === null) {
            return null;
        }

        return $this->effectiveQuantity() * (float) $cost;
    }

    /**
     * Recursive cost (includes sub-assemblies)
     *
     * @param array $visited
     *
     * @return float|null
     */
    public function totalCost(array $visited = []): ?float
    {
        if (! $this->childPart) {
            return null;
        }

        if (in_array($this->child_part_id, $visited)) {
            return 0;
        }

        $visited[] = $this->child_part_id;

        if ($this->childPart->hasBom()) {
            return $this->effectiveQuantity()
                * ($this->childPart->bomCost($visited) ?? 0);
        }

        return $this->lineCost();
    }
}
