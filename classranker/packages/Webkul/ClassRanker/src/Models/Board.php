<?php

namespace Webkul\ClassRanker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\ClassRanker\Contracts\Board as BoardContract;

class Board extends Model implements BoardContract
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
    ];

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return null;
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
