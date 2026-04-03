<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single answer option for a LearningQuestion.
 *
 * Stores the answer text and whether it is the correct answer for the
 * associated question.
 *
 * Relationships defined in this model include:
 * - question(): The LearningQuestion that this answer belongs to.
 * Example usage of relationships:
 * ```php
 * $answer = LearningAnswer::find(1);
 * $question = $answer->question; // Get the associated question
 * ```
 *
 * Accessor methods include:
 * - getAnswerAttribute(): Returns the answer text, optionally formatted.
 * - getIsCorrectAttribute(): Returns whether the answer is correct as a boolean.
 * - getCorrectLabelAttribute(): Returns a human-readable label for the answer correctness.
 * Example usage of accessors:
 * ```php
 * $answer = LearningAnswer::find(1);
 * $answerText = $answer->answer; // Get the answer text
 * $isCorrect = $answer->is_correct; // Check if this answer is correct
 * $correctLabel = $answer->correct_label; // Get "Correct" or "Incorrect"
 * ```
 *
 * Query scopes include:
 * - scopeAnswerOptions($query, $questionId): Filter answers by question ID.
 * - scopeIncorrect($query): Filter answers to only include incorrect ones.
 * - scopeForQuestion($query, $questionId): Filter answers for a specific question.
 * - scopeIsCorrect($query): Filter answers to only include correct ones.
 * - scopeIsIncorrect($query): Filter answers to only include incorrect ones.
 * Example usage of query scopes:
 * ```php
 * $questionId = 1;
 * $answerOptions = LearningAnswer::answerOptions($questionId)->get(); // Get all answers for a question
 * $incorrectAnswers = LearningAnswer::incorrect()->get(); // Get all incorrect answers
 * $answersForQuestion = LearningAnswer::forQuestion($questionId)->get(); // Get answers for a specific question
 * $correctAnswers = LearningAnswer::isCorrect()->get(); // Get all correct answers
 * $incorrectAnswers = LearningAnswer::isIncorrect()->get(); // Get all incorrect answers
 * ```
 */
class LearningAnswer extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningAnswerFactory>
     */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'question_id',
        'answer',
        'is_correct',
    ];

    /**
     * Get the question this answer belongs to.
     *
     * @return BelongsTo<LearningQuestion,LearningAnswer>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LearningQuestion::class, 'question_id');
    }

    /**
     * Get the answer text (optionally formatted).
     *
     * @param string|null $value
     *
     * @return string
     */
    public function getAnswerAttribute($value): string
    {
        return trim($value);
    }

    /**
     * Get whether the answer is correct as a boolean.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function getIsCorrectAttribute($value): bool
    {
        return (bool) $value;
    }

    /**
     * Get a human-readable label for the answer correctness.
     *
     * @return string
     */
    public function getCorrectLabelAttribute(): string
    {
        return $this->is_correct ? 'Correct' : 'Incorrect';
    }

    /**
     * Get the answer options for the associated question.
     *
     * @param Builder<LearningAnswer> $query The query builder instance.
     * @param int $questionId The ID of the question to filter by.
     *
     * @return Builder<LearningAnswer> The query builder with the question scope applied.
     */
    public function scopeAnswerOptions(Builder $query, int $questionId): Builder
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Get the incorrect answers for the associated question.
     *
     * @param Builder<LearningAnswer> $query The query builder instance.
     *
     * @return Builder<LearningAnswer> The query builder with the incorrect answer scope applied.
     */
    public function scopeIncorrect(Builder $query): Builder
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope a query to only include answers for a specific question.
     *
     * @param Builder<LearningAnswer> $query The query builder instance.
     * @param int $questionId The ID of the question to filter by.
     *
     * @return Builder<LearningAnswer> The query builder with the question scope applied.
     */
    public function scopeForQuestion(Builder $query, int $questionId): Builder
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Scope a query to only include answers that are correct.
     *
     * @param Builder<LearningAnswer> $query The query builder instance.
     *
     * @return Builder<LearningAnswer> The query builder with the correct answer scope applied.
     */
    public function scopeIsCorrect(Builder $query): Builder
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope a query to only include answers that are incorrect.
     *
     * @param Builder<LearningAnswer> $query The query builder instance.
     *
     * @return Builder<LearningAnswer> The query builder with the incorrect
     * answer scope applied.
     */
    public function scopeIsIncorrect(Builder $query): Builder
    {
        return $query->where('is_correct', false);
    }
}
