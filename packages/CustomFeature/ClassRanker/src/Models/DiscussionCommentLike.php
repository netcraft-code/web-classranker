<?php

namespace CustomFeature\ClassRanker\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionCommentLike extends Model
{
    protected $fillable = ['discussion_comment_id', 'customer_id'];

    public function comment()
    {
        return $this->belongsTo(DiscussionComment::class, 'discussion_comment_id');
    }
}