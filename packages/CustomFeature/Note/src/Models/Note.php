<?php

namespace CustomFeature\Note\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Note\Contracts\Note as NoteContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Note extends Model implements NoteContract
{
    protected $fillable = [
        'title',
        'short_title',
        'slug',
        'top_description',
        'content',
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

    public function assignments(): HasMany
    {
        return $this->hasMany(NoteAssignment::class);
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'note_assignments')->distinct();
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'note_assignments')->distinct();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'note_assignments')->distinct();
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'note_assignments')->distinct();
    }

    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'note_assignments')->distinct();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}