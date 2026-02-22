<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Customer\Models\Customer;
use CustomFeature\ClassRanker\Contracts\QuizAttempt as QuizAttemptContract;

class QuizAttempt extends Model implements QuizAttemptContract
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'customer_id',
        'score',
        'total_questions',
        'correct_answers',
        'started_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'score' => 'integer',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the quiz
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get customer answers
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class);
    }
}
