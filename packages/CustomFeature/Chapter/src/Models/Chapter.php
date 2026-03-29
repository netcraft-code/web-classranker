<?php

namespace CustomFeature\Chapter\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Contracts\Chapter as ChapterContract;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Note\Models\Note;
use CustomFeature\Pdf\Models\Pdf;
use CustomFeature\Question\Models\Question;
use CustomFeature\Quiz\Models\Quiz;     
use CustomFeature\Quiz\Models\QuizChapter;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Video\Models\Video;
use CustomFeature\Video\Models\VideoAssignment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Chapter extends Model implements ChapterContract
{
    protected $fillable = [
        'title',
        'code',
        'avatar',
        'status',
        'available_from',
        'board_id',
        'grade_id',
        'subject_id',
        'book_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getAvatarUrlAttribute()
    {
        return $this->avatar
            ? Storage::url($this->avatar)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Base Relations
    |--------------------------------------------------------------------------
    */

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Questions
    |--------------------------------------------------------------------------
    */

    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'question_assignments',
            'chapter_id',
            'question_id'
        )->with('questionItems');
    }

    public function activeQuestions()
    {
        return $this->questions()->active();
    }

    /*
    |--------------------------------------------------------------------------
    | Videos
    |--------------------------------------------------------------------------
    */

    public function videos()
    {
        return $this->belongsToMany(
            Video::class,
            'video_assignments',
            'chapter_id',
            'video_id'
        );
    }

    public function activeVideos()
    {
        return $this->videos()->active();
    }

    public function activeVideosWithActiveItems()
    {
        return $this->activeVideos()
            ->whereHas('videoItems', fn ($q) => $q->active())
            ->with(['videoItems' => fn ($q) => $q->active()->orderBy('position')]);
    }

    /*
    |--------------------------------------------------------------------------
    | Quizzes
    |--------------------------------------------------------------------------
    */

    public function quizzes()
    {
        return $this->belongsToMany(
            Quiz::class,
            'quiz_chapters',
            'chapter_id',
            'quiz_id'
        );
    }

    public function activeQuizzes()
    {
        return $this->quizzes()->active();
    }

    public function pdfs()
    {
        return $this->belongsToMany(
            Pdf::class,
            'pdf_assignments',
            'chapter_id',
            'pdf_id'
        )->with('pdfItems');
    }

    public function activePdfs()
    {
        return $this->pdfs()->active();
    }

    public function notes()
    {
        return $this->belongsToMany(
            Note::class,
            'note_assignments',
            'chapter_id',
            'note_id'
        );
    }

    public function activeNotes()
    {
        return $this->notes()->active();
    }

    public function quizChapters()
    {
        return $this->hasMany(QuizChapter::class, 'chapter_id');
    }

    public function videoAssignments()
    {
        return $this->hasMany(VideoAssignment::class, 'chapter_id');
    }
}