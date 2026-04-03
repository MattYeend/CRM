<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a job title that can be assigned to users.
 *
 * Provides a set of predefined title and group constants covering C-Suite,
 * executive, and director level positions, and exposes a users relationship
 * to retrieve all users holding the title.
 *
 * Relationships defined in this model include:
 * - users(): One-to-many relationship to User records assigned to this
 *      job title.
 * - creator(): Belongs-to relationship to the User who created the job
 *      title record.
 * - updater(): Belongs-to relationship to the User who last updated the
 *      job title record.
 * - deleter(): Belongs-to relationship to the User who deleted the job
 *      title record (if soft-deleted).
 * - restorer(): Belongs-to relationship to the User who restored the job
 *      title record (if soft-deleted).
 * Example usage of relationships:
 * ```php
 * $jobTitle = JobTitle::find(1);
 * $users = $jobTitle->users; // Get all users holding this title
 * $creator = $jobTitle->creator; // Get the user that created this record
 * $updater = $jobTitle->updater; // Get the user that last updated this record
 * $deleter = $jobTitle->deleter; // Get the user that deleted this record (if applicable)
 * $restorer = $jobTitle->restorer; // Get the user that restored this record (if applicable)
 * ```
 *
 * Accessor methods include:
 * - getTitleAttribute(): Returns the job title, applying a test prefix
 *      if the record is marked as a test.
 * - getIsCsuiteAttribute(): Returns a boolean indicating whether this
 *      title belongs to the C-Suite group.
 * - getIsExecutiveAttribute(): Returns a boolean indicating whether this
 *      title belongs to the executive group.
 * - getIsDirectorAttribute(): Returns a boolean indicating whether this
 *      title belongs to the director group.
 * - getUserCountAttribute(): Returns the total number of users assigned
 *      to this job title.
 * Example usage of accessors:
 * ```php
 * $jobTitle = JobTitle::find(1);
 * $title = $jobTitle->title; // Get the title with test prefix if applicable
 * $isCsuite = $jobTitle->is_csuite; // Check if this is a C-Suite title
 * $isDirector = $jobTitle->is_director; // Check if this is a director-level title
 * $userCount = $jobTitle->user_count; // Get the number of users with this title
 * ```
 *
 * Query scopes include:
 * - scopeCsuite($query): Filter the query to only include C-Suite titles.
 * - scopeExecutive($query): Filter the query to only include executive-level
 *      titles.
 * - scopeDirectors($query): Filter the query to only include director-level
 *      titles.
 * - scopeInGroup($query, $group): Filter the query to only include titles
 *      belonging to a given group value.
 * - scopeReal($query): Filter the query to only include non-test records.
 * - scopeSearch($query, $term): Filter the query by title or short code
 *      using a single search term.
 * Example usage of query scopes:
 * ```php
 * $csuite = JobTitle::csuite()->get(); // Get all C-Suite titles
 * $directors = JobTitle::directors()->get(); // Get all director-level titles
 * $results = JobTitle::search('chief')->get(); // Search by title or short code
 * $real = JobTitle::real()->get(); // Exclude test records
 * ```
 */
class JobTitle extends Model
{
    /**
     * @use HasFactory<\Database\Factories\JobTitleFactory>
     * @use SoftDeletes<\Illuminate\Database\Eloquent\SoftDeletes>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        SoftDeletes,
        HasTestPrefix;

    /**
     * C-Suite title constants.
     */
    public const TITLE_CEO = 'Chief Executive Officer';
    public const TITLE_CTO = 'Chief Technology Officer';
    public const TITLE_CFO = 'Chief Financial Officer';
    public const TITLE_COO = 'Chief Operating Officer';
    public const TITLE_CMO = 'Chief Marketing Officer';
    public const TITLE_CIO = 'Chief Information Officer';
    public const TITLE_CRO = 'Chief Revenue Officer';
    public const TITLE_CPO = 'Chief Product Officer';
    public const TITLE_CSO = 'Chief Strategy Officer';
    public const TITLE_CHRO = 'Chief Human Resources Officer';

    /**
     * Executive title constants.
     */
    public const TITLE_PRES = 'President';
    public const TITLE_VP = 'Vice President';
    public const TITLE_EVP = 'Executive Vice President';
    public const TITLE_SVP = 'Senior Vice President';
    public const TITLE_MD = 'Managing Director';
    public const TITLE_DIR = 'Director';
    public const TITLE_SR_DIR = 'Senior Director';

    /**
     * Director title constants.
     */
    public const TITLE_TECH_DIR = 'Technical Director';
    public const TITLE_SALES_DIR = 'Sales Director';
    public const TITLE_MKT_DIR = 'Marketing Director';
    public const TITLE_FIN_DIR = 'Finance Director';
    public const TITLE_HR_DIR = 'HR Director';
    public const TITLE_OPS_DIR = 'Operations Director';
    public const TITLE_SUPPORT_DIR = 'Support Director';

    /**
     * All C-Suite level job titles.
     */
    public const GROUP_C_SUITE = [
        self::TITLE_CEO,
        self::TITLE_CTO,
        self::TITLE_CFO,
        self::TITLE_COO,
        self::TITLE_CMO,
        self::TITLE_CIO,
        self::TITLE_CRO,
        self::TITLE_CPO,
        self::TITLE_CSO,
        self::TITLE_CHRO,
    ];

    /**
     * All executive level job titles.
     */
    public const GROUP_EXECUTIVE = [
        self::TITLE_PRES,
        self::TITLE_VP,
        self::TITLE_EVP,
        self::TITLE_SVP,
        self::TITLE_MD,
        self::TITLE_DIR,
        self::TITLE_SR_DIR,
    ];

    /**
     * All director level job titles.
     */
    public const GROUP_DIRS = [
        self::TITLE_MD,
        self::TITLE_DIR,
        self::TITLE_SR_DIR,
        self::TITLE_TECH_DIR,
        self::TITLE_SALES_DIR,
        self::TITLE_MKT_DIR,
        self::TITLE_FIN_DIR,
        self::TITLE_HR_DIR,
        self::TITLE_OPS_DIR,
        self::TITLE_SUPPORT_DIR,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'title',
        'short_code',
        'group',
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
     * Get the users that hold this job title.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'job_title_id');
    }

    /**
     * Get the user that created the job title.
     *
     * @return BelongsTo<User,JobTitle>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the job title.
     *
     * @return BelongsTo<User,JobTitle>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the job title.
     *
     * @return BelongsTo<User,JobTitle>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the user that restored the job title.
     *
     * @return BelongsTo<User,JobTitle>
     */
    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Get the job title, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw title value from the database.
     *
     * @return string
     */
    public function getTitleAttribute($value): string
    {
        return $this->prefixTest($value);
    }

    /**
     * Determine whether this title belongs to the C-Suite group.
     *
     * Checks the raw title value against the GROUP_C_SUITE constant array.
     * Useful for applying elevated access rules or segmenting users by
     * seniority without loading group data separately.
     *
     * @return bool
     */
    public function getIsCsuiteAttribute(): bool
    {
        return in_array($this->attributes['title'] ?? '', self::GROUP_C_SUITE, true);
    }

    /**
     * Determine whether this title belongs to the executive group.
     *
     * Checks the raw title value against the GROUP_EXECUTIVE constant array.
     *
     * @return bool
     */
    public function getIsExecutiveAttribute(): bool
    {
        return in_array($this->attributes['title'] ?? '', self::GROUP_EXECUTIVE, true);
    }

    /**
     * Determine whether this title belongs to the director group.
     *
     * Checks the raw title value against the GROUP_DIRS constant array,
     * which includes managing directors, senior directors, and all
     * functional director roles.
     *
     * @return bool
     */
    public function getIsDirectorAttribute(): bool
    {
        return in_array($this->attributes['title'] ?? '', self::GROUP_DIRS, true);
    }

    /**
     * Get the total number of users assigned to this job title.
     *
     * Fires a query each time the accessor is called. Avoid using it in
     * loops without eager loading the count via withCount('users').
     *
     * @return int
     */
    public function getUserCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Scope a query to only include C-Suite titles.
     *
     * Filters by the GROUP_C_SUITE constant array using a whereIn clause.
     * Useful for queries that target senior leadership, such as reporting
     * or access-level filtering.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeCsuite(Builder $query): Builder
    {
        return $query->whereIn('title', self::GROUP_C_SUITE);
    }

    /**
     * Scope a query to only include executive-level titles.
     *
     * Filters by the GROUP_EXECUTIVE constant array using a whereIn clause.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeExecutive(Builder $query): Builder
    {
        return $query->whereIn('title', self::GROUP_EXECUTIVE);
    }

    /**
     * Scope a query to only include director-level titles.
     *
     * Filters by the GROUP_DIRS constant array using a whereIn clause,
     * covering managing directors, senior directors, and all functional
     * director roles.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeDirectors(Builder $query): Builder
    {
        return $query->whereIn('title', self::GROUP_DIRS);
    }

    /**
     * Scope a query to only include titles belonging to a given group value.
     *
     * Filters by the 'group' column directly. Useful when titles are stored
     * with an explicit group identifier rather than being derived from the
     * title string alone.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     * @param  string $group The group value to filter by.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeInGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Scope a query to only include non-test job title records.
     *
     * Filters out any records where the 'is_test' flag is true, ensuring
     * that queries return only real job titles.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeReal(Builder $query): Builder
    {
        return $query->where('is_test', false);
    }

    /**
     * Scope a query to search job titles by title or short code using a
     * single search term.
     *
     * Wraps the conditions in a grouped where clause to ensure correct
     * boolean precedence when chained with other scopes.
     *
     * @param  Builder<JobTitle> $query The query builder instance.
     * @param  string $term The search term to match against.
     *
     * @return Builder<JobTitle> The modified query builder instance.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where(function (Builder $q) use ($like) {
            $q->where('title', 'like', $like)
                ->orWhere('short_code', 'like', $like);
        });
    }
}
