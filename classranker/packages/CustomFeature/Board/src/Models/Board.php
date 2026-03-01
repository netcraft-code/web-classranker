<?php

namespace CustomFeature\Board\Models;

use CustomFeature\Board\Contracts\Board as BoardContract;
use CustomFeature\Grade\Models\Grade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
        'title',
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
