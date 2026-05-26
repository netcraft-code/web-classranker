<?php

namespace CustomFeature\ClassRanker\Models;

use CustomFeature\ClassRanker\Contracts\DiscussionComment as DiscussionCommentContract;
use CustomFeature\ClassRanker\Models\Customer\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussionComment extends Model implements DiscussionCommentContract
{
    use SoftDeletes;

    protected $fillable = [
        'discussion_id', 'customer_id', 'body',
        'likes_count', 'status',
        'deleted_by_type', 'deleted_by_id', 'edited_at', 'images',
    ];

    protected $casts = [
        'images'      => 'array',
        'status'      => 'boolean',
        'likes_count' => 'integer',
        'edited_at'   => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function discussion()
    {
        return $this->belongsTo(Discussion::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function likes()
    {
        return $this->hasMany(DiscussionCommentLike::class, 'discussion_comment_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isLikedBy(int $customerId): bool
    {
        return $this->likes()->where('customer_id', $customerId)->exists();
    }

    public function getIsEditedAttribute(): bool
    {
        return !is_null($this->edited_at);
    }
}