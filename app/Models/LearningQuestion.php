<?php

namespace App\Models;

use App\Traits\Learning\HasValidationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a question within a Learning record.
 *
 * Each question belongs to a single Learning and may have multiple answer
 * options, one or more of which may be marked as correct.
 *
 * The question text is stored in the 'question' attribute, and the related
 * answers are accessed via the 'answers' relationship. The model includes
 * methods to determine if the question is valid (has at least one correct and
 * one incorrect answer) and to retrieve the validation status for display
 * purposes. Query scopes are provided to filter questions by validity and
 * associated learning.
 *
 * Relationships in this model include:
 * - learning(): BelongsTo relationship to the Learning this
 *      question belongs to.
 * - answers(): HasMany relationship to the LearningAnswer
 *      options for this question.
 * - correctAnswers(): HasMany relationship to the correct
 *      LearningAnswer options for this question.
 * - incorrectAnswers(): HasMany relationship to the incorrect
 *      LearningAnswer options for this question.
 * Example usage of relationships:
 * ```php
 * $question = LearningQuestion::find(1);
 * $learning = $question->learning; // Get the parent learning
 * $answers = $question->answers; // Get all answer options for
 *  this question
 * $correctAnswers = $question->correctAnswers; // Get the correct
 *  answer options for this question
 * $incorrectAnswers = $question->incorrectAnswers; // Get the
 *  incorrect answer options for this question
 * ```
 *
 * Accessor methods include:
 * - getQuestionAttribute(): Applies the test prefix to the question
 *      text when marked as a test.
 * - hasCorrectAnswer(): Returns a boolean indicating whether this
 *      question has at least one correct answer.
 * - hasIncorrectAnswer(): Returns a boolean indicating whether this
 *      question has at least one incorrect answer.
 * - isValid(): Returns a boolean indicating whether this question is
 *      valid (has at least one correct and one incorrect answer).
 * - getValidationStatusAttribute(): Returns a string indicating the
 *      validation status ('valid' or 'invalid').
 * - getValidationStatusLabelAttribute(): Returns a human-readable label
 *      for the validation status.
 * - getValidationStatusColorAttribute(): Returns a color name or code
 *      representing the validation status.
 * - getValidationStatusIconAttribute(): Returns a string representing
 *      an icon for the validation status.
 * - getValidationStatusTooltipAttribute(): Returns a tooltip message
 *      providing additional information about the validation status.
 * - getValidationStatusBadgeAttribute(): Returns a string representing
 *      a badge class for the validation status.
 * - getValidationStatusTextAttribute(): Returns a human-readable
 *      text indicating the validation status.
 * - getValidationStatusClassAttribute(): Returns a CSS class
 *      name representing the validation status.
 * Example usage of accessors:
 * ```php
 * $question = LearningQuestion::find(1);
 * $questionText = $question->question; // Get the question text
 *  with test prefix if applicable
 * $hasCorrect = $question->has_correct_answer; // Check if the
 *  question has a correct answer
 * $hasIncorrect = $question->has_incorrect_answer; // Check if
 *  the question has an incorrect answer
 * $isValid = $question->is_valid; // Check if the question is valid
 * $validationStatus = $question->validation_status; // Get the
 *  validation status ('valid' or 'invalid')
 * $validationLabel = $question->validation_status_label; // Get
 *  the validation status label
 * $validationColor = $question->validation_status_color; // Get
 *  the validation status color
 * $validationIcon = $question->validation_status_icon; // Get
 *  the validation status icon
 * $validationTooltip = $question->validation_status_tooltip;
 *  // Get the validation status tooltip
 * $validationBadge = $question->validation_status_badge; // Get
 *  the validation status badge class
 * $validationText = $question->validation_status_text; // Get
 *  the validation status text
 * $validationClass = $question->validation_status_class; // Get
 *  the validation status CSS class
 * ```
 *
 * Query scopes include:
 * - scopeValid($query): Filter the query to only include
 *      valid questions.
 * - scopeInvalid($query): Filter the query to only include
 *      invalid questions.
 * - scopeForLearning($query, $learningId): Filter the query
 *      to only include questions for a given learning ID.
 * - scopeSearch($query, $term): Filter the query to only include
 *      questions containing a search term in the question text.
 * Example usage of query scopes:
 * ```php
 * $validQuestions = LearningQuestion::valid()->get(); // Get all
 *  valid questions
 * $invalidQuestions = LearningQuestion::invalid()->get(); // Get all
 *  invalid questions
 * $questionsForLearning = LearningQuestion::forLearning($learningId)
 *  ->get(); // Get questions for a specific learning
 * $searchResults = LearningQuestion::search('term')->get(); // Get
 *  questions containing 'term' in the question text
 * ```
 */
class LearningQuestion extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningQuestionFactory>
     * @use HasValidationStatus<\App\Traits\Learning\HasValidationStatus>
     */
    use HasFactory,
        HasValidationStatus;

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
     * Get the incorrect answer(s) for this question.
     *
     * @return HasMany<LearningAnswer>
     */
    public function incorrectAnswers(): HasMany
    {
        return $this->answers()->where('is_correct', false);
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

    /**
     * Determine if the question has any correct answers.
     *
     * @return bool True if at least one correct answer exists,
     * false otherwise.
     */
    public function hasCorrectAnswer(): bool
    {
        return $this->correctAnswers()->exists();
    }

    /**
     * Determine if the question has any incorrect answers.
     *
     * @return bool True if at least one incorrect answer exists,
     * false otherwise.
     */
    public function hasIncorrectAnswer(): bool
    {
        return $this->incorrectAnswers()->exists();
    }

    /**
     * Determine if the question is valid (has at least one correct and one
     * incorrect answer).
     *
     * @return bool True if the question is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return $this->hasCorrectAnswer() && $this->hasIncorrectAnswer();
    }

    /**
     * Scope a query to only include valid questions.
     *
     * @param  Builder<LearningQuestion> $query The query builder instance.
     *
     * @return Builder<LearningQuestion> The modified query builder instance.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->whereHas('answers', function ($q) {
            $q->where('is_correct', true);
        })->whereHas('answers', function ($q) {
            $q->where('is_correct', false);
        });
    }

    /**
     * Scope a query to only include invalid questions.
     *
     * @param  Builder<LearningQuestion> $query The query builder instance.
     *
     * @return Builder<LearningQuestion> The modified query builder instance.
     */
    public function scopeInvalid(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereDoesntHave('answers', function ($q) {
                $q->where('is_correct', true);
            })->orWhereDoesntHave('answers', function ($q) {
                $q->where('is_correct', false);
            });
        });
    }

    /**
     * Scope a query to only include questions for a given learning.
     *
     * @param  Builder<LearningQuestion> $query The query builder instance.
     * @param  int $learningId The ID of the learning to filter by.
     *
     * @return Builder<LearningQuestion> The modified query builder instance.
     */
    public function scopeForLearning(Builder $query, int $learningId): Builder
    {
        return $query->where('learning_id', $learningId);
    }

    /**
     * Scope a query to only include questions containing a search term in
     * the question text.
     *
     * @param  Builder<LearningQuestion> $query The query builder instance.
     * @param  string|null $term The search term to filter by.
     *
     * @return Builder<LearningQuestion> The modified query builder instance.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $like = "%{$term}%";

        return $query->where('question', 'like', $like);
    }
}
