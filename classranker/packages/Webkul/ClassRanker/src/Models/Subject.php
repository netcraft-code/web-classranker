<?php

namespace Webkul\ClassRanker\Models;

use Webkul\ClassRanker\Contracts\Subject as SubjectContract;
use Illuminate\Database\Eloquent\Model;

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
        'is_premium',
        'avatar',
        'status',
        'board_id',
        'grade_id',
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

    public function books()
    {
        return $this->hasMany(Book::class);
    }
}
