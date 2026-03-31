<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a question within a Learning record.
 *
 * Each question belongs to a single Learning and may have multiple answer
 * options, one or more of which may be marked as correct.
 */
class LearningQuestion extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningQuestionFactory>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory,
        HasTestPrefix;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'learning_id',
        'question',
    ];

    /**
     * Get the learning this question belongs to.
     *
     * @return BelongsTo<Learning,LearningQuestion>
     */
    public function learning(): BelongsTo
    {
        return $this->belongsTo(Learning::class);
    }

    /**
     * Get all answer options for this question.
     *
     * @return HasMany<LearningAnswer>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(LearningAnswer::class, 'question_id');
    }

    /**
     * Get the correct answer(s) for this question.
     *
     * @return HasMany<LearningAnswer>
     */
    public function correctAnswers(): HasMany
    {
        return $this->answers()->where('is_correct', true);
    }

    /**
     * Get the question text, applying the test prefix when marked as a test.
     *
     * @param  string|null  $value  The raw question value from the database.
     *
     * @return string
     */
    public function getQuestionAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
