<?php

namespace Webkul\ClassRanker\Models;

use Webkul\ClassRanker\Contracts\Book as BookContract;
use Illuminate\Database\Eloquent\Model;

class Book extends Model implements BookContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'code',
        'writer',
        'publisher',
        'edition',
        'publication_year',
        'total_pages',
        'avatar',
        'status',
        'board_id',
        'grade_id',
        'subject_id',
    ];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? url('/storage/' . $this->avatar) : null;
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

    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }
}
