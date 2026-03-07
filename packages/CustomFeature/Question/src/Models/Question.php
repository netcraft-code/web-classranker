<?php

namespace CustomFeature\Question\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Question\Contracts\Question as QuestionContract;
use CustomFeature\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Question extends Model implements QuestionContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'short_title',
        'slug',
        'top_description',
        'bottom_description',
        'related_links',
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

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return null;
    }

    /**
     * Get the question items (sub-questions)
     */
    public function questionItems(): HasMany
    {
        return $this->hasMany(QuestionItem::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Get the FAQs
     */
    public function faqs(): HasMany
    {
        return $this->hasMany(QuestionFaq::class)->orderBy('order');
    }

    /**
     * Get the assignments (many-to-many relationships)
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(QuestionAssignment::class);
    }

    /**
     * Get all boards this question is assigned to
     */
    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'question_assignments')
            ->distinct();
    }

    /**
     * Get all grades this question is assigned to
     */
    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'question_assignments')
            ->distinct();
    }

    /**
     * Get all subjects this question is assigned to
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'question_assignments')
            ->distinct();
    }

    /**
     * Get all books this question is assigned to
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'question_assignments')
            ->distinct();
    }

    /**
     * Get all chapters this question is assigned to
     */
    public function chapters()
    {
        return $this->hasManyThrough(
            Chapter::class,
            QuestionAssignment::class,
            'question_id',
            'id',
            'id',
            'chapter_id'
        );
    }
}
