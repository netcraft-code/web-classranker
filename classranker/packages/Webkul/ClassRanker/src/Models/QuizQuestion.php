<?php

namespace Webkul\ClassRanker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\ClassRanker\Contracts\QuizQuestion as QuizQuestionContract;

class QuizQuestion extends Model implements QuizQuestionContract
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_order',
    ];

    protected $casts = [
        'question_order' => 'integer',
    ];

    /**
     * Get the quiz
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get question options
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class)->orderBy('option_order');
    }

    /**
     * Get the correct option
     */
    public function correctOption()
    {
        return $this->hasOne(QuizOption::class)->where('is_correct', 1);
    }

    /**
     * Get customer answers
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
