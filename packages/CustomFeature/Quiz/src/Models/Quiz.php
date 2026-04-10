<?php

namespace CustomFeature\Quiz\Models;

use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Quiz\Contracts\Quiz as QuizContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\User\Models\Admin;

class Quiz extends Model implements QuizContract
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'is_premium',
        'created_by',
    ];

    protected $casts = [
        'status'     => 'boolean',
        'is_premium' => 'boolean',
    ];

    /**
     * Get the creator (admin)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get quiz chapters
     */
    public function quizChapters(): HasMany
    {
        return $this->hasMany(QuizChapter::class);
    }

    /**
     * Get quiz questions
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('question_order');
    }

    /**
     * Get quiz attempts
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get all chapters through quiz_chapters
     */
    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'quiz_chapters');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
