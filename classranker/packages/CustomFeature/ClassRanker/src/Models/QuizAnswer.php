<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_attempt_id',
        'quiz_question_id',
        'quiz_option_id',
        'correct_option_id',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the attempt
     */
    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    /**
     * Get the question
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    /**
     * Get the user's selected option
     */
    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'quiz_option_id');
    }

    /**
     * Get the correct option
     */
    public function correctOption(): BelongsTo
    {
        return $this->belongsTo(QuizOption::class, 'correct_option_id');
    }
}