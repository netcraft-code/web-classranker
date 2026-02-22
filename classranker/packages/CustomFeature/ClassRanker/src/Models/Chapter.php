<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use CustomFeature\ClassRanker\Contracts\Chapter as ChapterContract;

class Chapter extends Model implements ChapterContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'code',
        'avatar',
        'status',
        'board_id',
        'grade_id',
        'subject_id',
        'book_id',
    ];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return null;
    }

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
}
