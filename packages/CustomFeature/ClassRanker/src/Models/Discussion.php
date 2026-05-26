<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\Discussion as DiscussionContract;
use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;
use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discussion extends Model implements DiscussionContract
{
    protected $fillable = [
        'title', 'description',
        'creator_type', 'creator_id',
        'board_id', 'grade_id', 'subject_id', 'book_id', 'chapter_id',
        'views_count', 'likes_count', 'comments_count',
        'status', 'is_resolved',
    ];

    protected $casts = [
        'status'         => 'boolean',
        'views_count'    => 'integer',
        'likes_count'    => 'integer',
        'comments_count' => 'integer',
        'is_resolved'    => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * Polymorphic: creator can be Admin or Customer.
     */
    public function creator()
    {
        return $this->morphTo();
    }

    public function hashtags()
    {
        return $this->belongsToMany(Hashtag::class, 'discussion_hashtag');
    }

    public function comments()
    {
        return $this->hasMany(DiscussionComment::class, 'discussion_id')
                    ->where('status', true)
                    ->orderByDesc('created_at');
    }

    public function likes()
    {
        return $this->hasMany(DiscussionLike::class, 'discussion_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isLikedBy(int $customerId): bool
    {
        return $this->likes()->where('customer_id', $customerId)->exists();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'grade_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
}