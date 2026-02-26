<?php

namespace CustomFeature\Grade\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Contracts\Grade as GradeContract;
use CustomFeature\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'title',
        'avatar',
        'status',
        'board_id',
    ];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return null;
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
