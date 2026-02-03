<?php

namespace Webkul\ClassRanker\Models;

use Webkul\ClassRanker\Contracts\Grade as GradeContract;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model implements GradeContract
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
    ];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? url('/storage/' . $this->avatar) : null;
    }

    /**
     * Get the board that owns the grade.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
