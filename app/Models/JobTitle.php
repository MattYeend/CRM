<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     *
     * @var array<int, string>
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
     *
     * @var array<int, string>
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
     *
     * @var array<int, string>
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
     * Get the users that have the job title
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'job_title_id');
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
}
