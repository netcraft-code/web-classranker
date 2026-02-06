<?php

namespace Webkul\ClassRanker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\ClassRanker\Contracts\QuestionFaq as QuestionFaqContract;

class QuestionFaq extends Model implements QuestionFaqContract
{
    protected $table = 'question_faqs';

    protected $fillable = [
        'question_id',
        'question',
        'answer',
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