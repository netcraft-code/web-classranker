<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\ClassRanker\Contracts\QuizOption as QuizOptionContract;

class QuizOption extends Model implements QuizOptionContract
{
    use HasFactory;

    protected $fillable = [
        'quiz_question_id',
        'option_text',
        'is_correct',
        'use_tinymce',
        'option_order',
    ];

    protected $casts = [
        'is_correct'   => 'boolean',
        'option_order' => 'integer',
    ];

    /**
     * Get the question
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
