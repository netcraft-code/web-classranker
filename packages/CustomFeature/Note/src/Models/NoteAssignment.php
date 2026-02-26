<?php

namespace CustomFeature\Note\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Note\Contracts\NoteAssignment as NoteAssignmentContract;
use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;

class NoteAssignment extends Model implements NoteAssignmentContract
{
    protected $fillable = [
        'note_id', 'board_id', 'grade_id',
        'subject_id', 'book_id', 'chapter_id',
    ];

    public function note(): BelongsTo    { return $this->belongsTo(Note::class); }
    public function board(): BelongsTo   { return $this->belongsTo(Board::class); }
    public function grade(): BelongsTo   { return $this->belongsTo(Grade::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function book(): BelongsTo    { return $this->belongsTo(Book::class); }
    public function chapter(): BelongsTo { return $this->belongsTo(Chapter::class); }
}