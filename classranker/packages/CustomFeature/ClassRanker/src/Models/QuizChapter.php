<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\ClassRanker\Contracts\QuizChapter as QuizChapterContract;

class QuizChapter extends Model implements QuizChapterContract
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'board_id',
        'grade_id',
        'subject_id',
        'book_id',
        'chapter_id',
    ];

    /**
     * Get the quiz
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the board
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the grade
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Get the subject
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the book
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the chapter
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}

