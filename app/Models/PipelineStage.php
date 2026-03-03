<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineStage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pipeline_id',
        'name',
        'position',
        'is_won_stage',
        'is_lost_stage',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the pipeline that owns the stage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the deals for the stage.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
