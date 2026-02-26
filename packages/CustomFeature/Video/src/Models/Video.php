<?php

namespace CustomFeature\Video\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Video\Contracts\Video as VideoContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model implements VideoContract
{
    protected $fillable = [
        'title',
        'short_title',
        'slug',
        'top_description',
        'bottom_description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'is_premium',
    ];

    protected $casts = [
        'status'     => 'boolean',
        'is_premium' => 'boolean',
    ];

    public function videoItems(): HasMany
    {
        return $this->hasMany(VideoItem::class)->orderBy('position');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(VideoAssignment::class);
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'video_assignments')->distinct();
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'video_assignments')->distinct();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'video_assignments')->distinct();
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'video_assignments')->distinct();
    }

    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'video_assignments')->distinct();
    }
}