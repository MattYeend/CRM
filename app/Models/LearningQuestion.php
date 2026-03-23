<?php

namespace App\Models;

use App\Traits\HasTestPrefix;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
     * @var array<int, string>
     */
    protected $fillable = [
        'learning_id',
        'question',
    ];

    /**
     * The learning this question belongs to.
     *
     * @return BelongsTo
     */
    public function learning(): BelongsTo
    {
        return $this->belongsTo(Learning::class);
    }

    /**
     * The answers for this question.
     *
     * @return HasMany
     */
    public function answers(): HasMany
    {
        return $this->hasMany(LearningAnswer::class, 'question_id');
    }

    /**
     * Get the correct answer(s) for this question.
     *
     * @return HasMany
     */
    public function correctAnswers(): HasMany
    {
        return $this->answers()->where('is_correct', true);
    }

    /**
     * Get the learning question.
     *
     * Applies the test prefix when the learning is marked as a test.
     *
     * @param  string|null  $value  The raw learning title from the database.
     *
     * @return string
     */
    public function getQuestionAttribute($value): string
    {
        return $this->prefixTest($value);
    }
}
