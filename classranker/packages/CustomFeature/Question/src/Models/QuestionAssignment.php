<?php

namespace CustomFeature\Question\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Question\Contracts\QuestionAssignment as QuestionAssignmentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAssignment extends Model implements QuestionAssignmentContract
{
    protected $fillable = [
        'question_id',
        'board_id',
        'grade_id',
        'subject_id',
        'book_id',
        'chapter_id',
    ];

    /**
     * Get the question
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
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