<?php

namespace CustomFeature\Quiz\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Quiz\Contracts\QuizChapter as QuizChapterContract;
use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;

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

