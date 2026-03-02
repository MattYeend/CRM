<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'filename',
        'disk',
        'path',
        'uploaded_by',
        'size',
        'mime',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the parent attachable model (contact, deal, etc.).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded the attachment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
