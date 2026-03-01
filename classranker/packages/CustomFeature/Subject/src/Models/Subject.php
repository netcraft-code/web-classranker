<?php

namespace CustomFeature\Subject\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Book\Models\Book;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Contracts\Subject as SubjectContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Subject extends Model implements SubjectContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'avatar',
        'status',
        'board_id',
        'grade_id',
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

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
