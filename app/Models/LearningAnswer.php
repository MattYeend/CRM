<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single answer option for a LearningQuestion.
 *
 * Stores the answer text and whether it is the correct answer for the
 * associated question.
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
}
