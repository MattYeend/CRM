<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'company_id',
        'contact_id',
        'owner_id',
        'pipeline_id',
        'stage_id',
        'value',
        'currency',
        'close_date',
        'status',
        'meta'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'close_date' => 'date',
        'meta' => 'array',
    ];

    /**
     * Get the company that owns the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the contact that owns the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the owner (user) that owns the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the pipeline that owns the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get the stage that owns the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stage()
    {
        return $this->belongsTo(PipelineStage::class, 'stage_id');
    }

    /**
     * Get the tasks for the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function tasks()
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get the notes for the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the attachments for the deal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
