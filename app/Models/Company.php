<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'industry',
        'website',
        'phone',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the contacts for the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function contacts()
    // {
    //     return $this->hasMany(Contact::class);
    // }

    /** Get the deals for the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function deals()
    // {
    //     return $this->hasMany(Deal::class);
    // }

    /**
     * Get the invoices for the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function invoices()
    // {
    //     return $this->hasMany(Invoice::class);
    // }

    /**
     * Get all of the company's attachments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    // public function attachments()
    // {
    //     return $this->morphMany(Attachment::class, 'attachable');
    // }
}
