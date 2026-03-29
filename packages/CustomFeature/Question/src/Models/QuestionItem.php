<?php

namespace CustomFeature\Question\Models;

use CustomFeature\ClassRanker\Traits\HasBookmarks;
use CustomFeature\Question\Contracts\QuestionItem as QuestionItemContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionItem extends Model implements QuestionItemContract
{
    use HasBookmarks;
    
    protected $fillable = [
        'question_id',
        'question_number',
        'question_title',
        'question',
        'answer',
        'page_number',
        'video_solution',
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