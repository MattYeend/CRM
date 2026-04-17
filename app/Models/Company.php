<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a company record within the CRM.
 *
 * Holds identity, address, and primary contact information for a company,
 * and acts as the root of several key relationships including deals,
 * invoices, activities, tasks, notes, and attachments.
 *
 * Relationships defined in this model include:
 * - deals(): One-to-many relationship to Deal records associated with the
 *      company.
 * - invoices(): One-to-many relationship to Invoice records associated with
 *      the company.
 * - attachments(): Polymorphic one-to-many relationship to Attachment records
 *      associated with the company.
 * - activities(): Polymorphic one-to-many relationship to Activity records
 *      associated with the company.
 * - tasks(): Polymorphic one-to-many relationship to Task records associated
 *      with the company.
 * - notes(): Polymorphic one-to-many relationship to Note records associated
 *      with the company.
 * - creator(): Belongs-to relationship to the User who created the company
 *      record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      company record.
 * - deleter(): Belongs-to relationship to the User who deleted the company
 *      record (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the company
 *      record (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $company = Company::find(1);
 * $deals = $company->deals; // Get all deals for the company
 * $invoices = $company->invoices; // Get all invoices for the company
 * $attachments = $company->attachments; // Get all attachments for the company
 * $activities = $company->activities; // Get all activities for the company
 * $tasks = $company->tasks; // Get all tasks for the company
 * $notes = $company->notes; // Get all notes for the company
 * $creator = $company->creator; // Get the user that created the company
 * $updater = $company->updater; // Get the user that last updated the company
 * $deleter = $company->deleter; // Get the user that deleted the
 * company (if applicable)
 * $restorer = $company->restorer; // Get the user that restored the
 * company (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getNameAttribute(): Returns the company name, applying a test prefix
 *      if the company is marked as a test record.
 * - getContactFullNameAttribute(): Returns the full name of the primary
 *      contact by combining the first and last name.
 * - getFullAddressAttribute(): Returns the full postal address of the
 *      company as a single formatted string.
 * - getHasDealsAttribute(): Returns a boolean indicating whether the
 *      company has any open (non-deleted) deals.
 * - getHasOutstandingInvoicesAttribute(): Returns a boolean indicating
 *      whether the company has any outstanding (unpaid) invoices.
 * - getWebsiteHostAttribute(): Returns the host portion of the company's
 *      website URL without the scheme or trailing slash, or null if no
 *      website is set.
 * Example usage of accessors:
 * ```php
 * $company = Company::find(1);
 * $name = $company->name; // Get the company name with test prefix if
 * applicable
 * $contactFullName = $company->contact_full_name; // Get the full name
 * of the primary contact
 * $fullAddress = $company->full_address; // Get the full postal address
 * of the company
 * $hasDeals = $company->has_deals; // Check if the company has any open
 * deals
 * $hasOutstandingInvoices = $company->has_outstanding_invoices; // Check
 * if the company has any outstanding invoices
 * $websiteHost = $company->website_host; // Get the host portion of the
 * company's website URL
 * ```
 *
 * Query scopes include:
 * - scopeReal($query): Filter the query to only include non-test companies.
 * - scopeInIndustry($query, $industry): Filter the query to only include
 *      companies in a given industry or list of industries.
 * - scopeInCountry($query, $country): Filter the query to only include
 *      companies in a given country or list of countries.
 * - scopeWithDeals($query): Filter the query to only include companies that
 *      have at least one deal.
 * - scopeWithoutDeals($query): Filter the query to only include companies
 *      that have no deals.
 * - scopeWithOutstandingInvoices($query): Filter the query to only include
 *      companies that have at least one outstanding (unpaid) invoice.
 * Example usage of query scopes:
 * ```php
 * $realCompanies = Company::real()->get(); // Get only non-test
 * companies
 * $techCompanies = Company::inIndustry('Technology')->get(); // Get
 * companies in the Technology industry
 * $usCompanies = Company::inCountry('United States')->get(); // Get
 *  the United States
 * $companiesWithDeals = Company::withDeals()->get(); // Get companies
 * that have at least one deal
 * $companiesWithoutDeals = Company::withoutDeals()->get(); // Get companies
 * that have no deals
 * $companiesWithOutstandingInvoices = Company::withOutstandingInvoices()
 * ->get(); // Get companies that have at least one outstanding invoice
 * ```
 */
class Company extends Model
{
    /**
     * @use HasFactory<\Database\Factories\CompanyFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'industry_id',
        'website',
        'phone',
        'address',
        'city',
        'region',
        'postal_code',
        'country',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
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
        'is_test' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'restored_at' => 'datetime',
    ];

    /**
     * Get the deals for the company.
     *
     * @return HasMany<Deal>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the invoices for the company.
     *
     * @return HasMany<Invoice>
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get all attachments associated with the company.
     *
     * @return MorphMany<Attachment>
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Get all activities associated with the company.
     *
     * @return MorphMany<Activity>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    /**
     * Get all tasks associated with the company.
     *
     * @return MorphMany<Task>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Get all notes associated with the company.
     *
     * @return MorphMany<Note>
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the industry associated with the company
     *
     * @return BelongsTo<Industry,Company>
     */
    public function industries(): BelongsTo
    {
        return $this->belongsTo(Industry::class);
    }

    /**
     * Get the user that created the company.
     *
     * @return BelongsTo<User,Company>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the company.
     *
     * @return BelongsTo<User,Company>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the company.
     *
     * @return BelongsTo<User,Company>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the company.
     *
     * @return BelongsTo<User,Company>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the company name, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw name value from the database.
     *
     * @return string
     */
    public function getNameAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Get the primary contact's full name.
     *
     * Combines the contact's first and last name into a single string.
     * Handles cases where one or both names may be missing gracefully.
     * Ensures that extra spaces are trimmed when one of the names is
     * not provided.
     *
     * @return string
     */
    public function getContactFullNameAttribute(): string
    {
        return trim("{$this->contact_first_name} {$this->contact_last_name}");
    }

    /**
     * Get the company's full postal address as a single formatted string.
     *
     * Combines the address, city, region, postal code, and country
     * into a single string.
     * Filters out any empty components to avoid extra commas and
     * spaces in the output.
     * Ensures that the resulting address is clean and properly
     * formatted even when some components are missing.
     * Useful for displaying the company's address in the UI without
     * needing to format it manually each time.
     * Note that the formatting is simple and does not account for
     * international address formats, which may require different
     * ordering or additional components.
     *
     * @return string
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->region,
            $this->postal_code,
            $this->country,
        ])->filter()->implode(', ');
    }

    /**
     * Determine whether the company has any open (non-deleted) deals.
     *
     * Checks for the existence of related deals that are not
     * soft-deleted.
     * Returns true if at least one open deal exists, otherwise false.
     * This is useful for quickly assessing whether the company
     * has active sales opportunities without needing to load
     * all deal records.
     * Note that this method does not consider the status of the
     * deals (e.g., won, lost) and only checks for their existence.
     *
     * @return bool
     */
    public function getHasDealsAttribute(): bool
    {
        return $this->deals()->exists();
    }

    /**
     * Determine whether the company has any outstanding
     * (unpaid) invoices.
     *
     * Checks for the existence of related invoices that have a
     * status other than 'paid'.
     * Returns true if at least one outstanding invoice exists,
     * otherwise false.
     * This is useful for quickly assessing whether the company
     * has any pending financial obligations without needing to
     * load all invoice records.
     * Note that this method considers any invoice that is not
     * marked as 'paid' as outstanding, including those in
     * draft or overdue status.
     *
     * @return bool
     */
    public function getHasOutstandingInvoicesAttribute(): bool
    {
        return $this->invoices()->where(
            'status',
            '!=',
            Invoice::STATUS_PAID
        )->exists();
    }

    /**
     * Get the company's website host without the scheme or trailing
     * slash, returning null when no website is set.
     *
     * Parses the website URL to extract the host component,
     * removing any 'www.' prefix for cleaner display.
     * Returns null if the website attribute is blank or if
     * the URL cannot be parsed, ensuring that the accessor
     * handles edge cases gracefully.
     * This is useful for displaying a simplified version of
     * the company's website in the UI, such as in lists or
     * summaries, without showing the full URL.
     * Note that this accessor does not validate the URL format
     * and assumes that the website attribute, if present,
     * is a valid URL.
     *
     * @return string|null
     */
    public function getWebsiteHostAttribute(): ?string
    {
        if (blank($this->website)) {
            return null;
        }

        $host = parse_url($this->website, PHP_URL_HOST);

        return $host ? ltrim($host, 'www.') : null;
    }

    /**
     * Scope a query to only include non-test companies.
     *
     * This scope filters the query to include only companies
     * where the 'is_test' attribute is false, effectively
     * excluding any companies that are marked as test records.
     * This is useful for ensuring that queries return only real
     * company records in the system, which is important for
     * accurate reporting and analysis.
     *
     * @param  Builder<Company>  $query The query builder instance.
     *
     * @return Builder<Company> The modified query builder instance
     * with the test filter applied.
     */
    public function scopeReal($query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to only include companies in a given industry or
     * list of industries.
     *
     * This scope allows filtering companies based on their industry.
     * It accepts either a single industry as a string or multiple
     * industries as an array of strings. The scope applies the
     * appropriate where clause to the query builder to filter
     * the results accordingly.
     * When a single industry is provided, it uses a simple where
     * clause to match the 'industry' column. When an array of industries
     * is provided, it uses a whereIn clause to match any of the specified
     * industries. This flexibility makes it easy to filter companies
     * by one or more industries in a single query.
     *
     * @param  Builder<Company>  $query The query builder instance.
     * @param  string|array<int,string>  $industry The industry or
     * industries to filter by, provided as a string for a single
     * industry or an array of strings for multiple industries.
     *
     * @return Builder<Company> The modified query builder instance
     * with the industry filter applied.
     */
    public function scopeInIndustry(
        $query,
        int|array $industryId
    ): Builder {
        return is_array($industryId)
            ? $query->whereIn('industry_id', $industryId)
            : $query->where('industry_id', $industryId);
    }

    /**
     * Scope a query to only include companies in a given country or
     * list of countries.
     *
     * This scope allows filtering companies based on their country.
     * It accepts either a single country as a string or multiple
     * countries as an array of strings. The scope applies the
     * appropriate where clause to the query builder to filter the
     * results accordingly.
     * When a single country is provided, it uses a simple where clause
     * to match the 'country' column. When an array of countries is
     * provided, it uses a whereIn clause to match any of the specified
     * countries. This flexibility makes it easy to filter companies
     * by one or more countries in a single query.
     * Note that the country values should match the format stored in the
     * database (e.g., full country names, ISO codes) for the filtering
     * to work correctly.
     *
     * @param  Builder<Company>  $query The query builder instance.
     * @param  string|array<int,string>  $country The country or countries
     * to filter by, provided as a string for a single country or an array
     * of strings for multiple countries.
     *
     * @return Builder<Company> The modified query builder instance with
     * the country filter applied.
     */
    public function scopeInCountry(
        $query,
        string|array $country
    ): Builder {
        return is_array($country)
            ? $query->whereIn('country', $country)
            : $query->where('country', $country);
    }

    /**
     * Scope a query to only include companies that have at least one deal.
     *
     * This scope filters the query to include only companies that have
     * at least one related deal record. It uses the whereHas method
     * to check for the existence of related deals, ensuring that only
     * companies with active sales opportunities are included in the
     * results. This can be useful for segmenting companies based on
     * their engagement level or for targeting sales efforts towards
     * companies that already have some level of interaction.
     * Note that this scope considers only the existence of related
     * deal records and does not take into account the status or
     * attributes of those deals. A company with deleted (soft-deleted)
     * deals would not be included in the results, as those deals are
     * not considered active.
     *
     * @param  Builder<Company>  $query The query builder instance.
     *
     * @return Builder<Company> The modified query builder instance
     * with the deal filter applied.
     */
    public function scopeWithDeals($query): Builder
    {
        return $query->whereHas('deals');
    }

    /**
     * Scope a query to only include companies that have no deals.
     *
     * This scope filters the query to include only companies that
     * do not have any related deal records. It uses the whereDoesntHave
     * method to check for the absence of related deals, ensuring
     * that only companies without deals are included in the results.
     * This can be useful for identifying companies that may need
     * sales outreach or for segmenting companies based on their
     * engagement level.
     * Note that this scope considers only the existence of related
     * deal records and does not take into account the status or
     * attributes of those deals. A company with deleted (soft-deleted)
     * deals would still be included in the results, as those deals
     * are not considered active.
     *
     * @param  Builder<Company>  $query The query builder instance.
     *
     * @return Builder<Company> The modified query builder instance
     * with the no-deal filter applied.
     */
    public function scopeWithoutDeals($query): Builder
    {
        return $query->whereDoesntHave('deals');
    }

    /**
     * Scope a query to only include companies that have at least one
     * outstanding (unpaid) invoice.
     *
     * This scope filters the query to include only companies
     * that have at least one related invoice record with a status
     * other than 'paid'. It uses the whereHas method to check
     * for the existence of related invoices that are not marked
     * as paid, ensuring that only companies with pending financial
     * obligations are included in the results. This can be useful
     * for targeting collections efforts or for segmenting companies
     * based on their payment status.
     * Note that this scope considers any invoice that is not
     * marked as 'paid' as outstanding, including those in draft
     * or overdue status. A company with only paid invoices would not
     * be included in the results, while a company with at least one
     * unpaid invoice would be included regardless of the total number
     * of invoices.
     *
     * @param  Builder<Company>  $query The query builder instance.
     *
     * @return Builder<Company> The modified query builder instance
     * with the outstanding invoice filter applied.
     */
    public function scopeWithOutstandingInvoices($query): Builder
    {
        return $query->whereHas(
            'invoices',
            fn ($q) => $q->where(
                'status',
                '!=',
                Invoice::STATUS_PAID
            )
        );
    }
}
