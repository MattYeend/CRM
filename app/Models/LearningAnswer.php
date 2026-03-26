<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningAnswer extends Model
{
    /**
     * @use HasFactory<\Database\Factories\LearningAnswerFactory>
     * @use HasTestPrefix<\App\Traits\HasTestPrefix>
     */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'answer',
        'is_correct',
    ];

    /**
     * The question this answer belongs to.
     *
     * @return BelongsTo<LearningQuestion,LearningAnswer>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(LearningQuestion::class, 'question_id');
    }
}
