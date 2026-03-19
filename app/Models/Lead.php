<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * */
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'first_name',
        'last_name',
        'email',
        'phone',
        'source',
        'owner_id',
        'assigned_to',
        'assigned_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'restored_by',
        'is_test',
        'meta',
        'created_at',
        'updated_at',
        'deleted_at',
        'restored_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_test' => 'boolean',
        'meta' => 'array',
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * The owner of the lead.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The user assigned to the lead.
     *
     * @return BelongsTo
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Convert the lead to a contact.
     *
     * @return Contact
     */
    public function convertToContact(): Contact
    {
        $contact = new Contact([
            'company_id' => null,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'job_title' => null,
            'meta' => $this->meta,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $contact->save();

        return $contact;
    }

    /**
     * Convert the lead to a deal.
     *
     * @return Deal
     */
    public function convertToDeal(): Deal
    {
        $deal = new Deal([
            'company_id' => null,
            'contact_id' => null,
            'owner_id' => $this->owner_id,
            'pipeline_id' => null,
            'close_date' => null,
            'status' => Deal::STATUS_OPEN,
            'meta' => $this->meta,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ]);

        $deal->save();

        return $deal;
    }

    /**
     * Get the user that created the lead.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the lead.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the lead.
     *
     * @return BelongsTo
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the lead.
     *
     * @return BelongsTo
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get all of the lead attachments.
     *
     * @return MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all of the lead activities.
     *
     * @return MorphMany
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all of the lead tasks.
     *
     * @return MorphMany
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all of the lead notes.
     *
     * @return MorphMany
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
