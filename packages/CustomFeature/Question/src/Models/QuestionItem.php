<?php

namespace CustomFeature\Question\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Question\Contracts\QuestionItem as QuestionItemContract;

class QuestionItem extends Model implements QuestionItemContract
{
    protected $fillable = [
        'question_id',
        'question_number',
        'question_title',
        'question',
        'answer',
        'page_number',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the parent question
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}