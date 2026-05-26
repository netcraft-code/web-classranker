<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\DiscussionComment;
use CustomFeature\ClassRanker\Models\DiscussionCommentLike;
use CustomFeature\ClassRanker\Models\Discussion;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;

class DiscussionCommentRepository extends Repository
{
    public function model(): string
    {
        return DiscussionComment::class;
    }

    /**
     * Create comment and bump discussion counter.
     */
    public function addComment(int $discussionId, int $customerId, string $body, array $tempImagePaths = []): \CustomFeature\ClassRanker\Models\DiscussionComment
    {
        // Move temp images to permanent path
        $permanentPaths = [];

        foreach ($tempImagePaths as $tempPath) {
            // Security: ensure path is within temp folder
            if (!str_starts_with($tempPath, 'discussions/temp/')) continue;

            $filename      = basename($tempPath);
            $permanentPath = 'discussions/comments/' . $filename;

            if (Storage::disk('public')->exists($tempPath)) {
                Storage::disk('public')->move($tempPath, $permanentPath);
                $permanentPaths[] = Storage::disk('public')->url($permanentPath);
            }
        }

        $comment = $this->create([
            'discussion_id' => $discussionId,
            'customer_id'   => $customerId,
            'body'          => $body,
            'images'        => !empty($permanentPaths) ? $permanentPaths : null,
        ]);

        Discussion::where('id', $discussionId)->increment('comments_count');

        return $comment->load('customer');
    }

    /**
     * Edit own comment.
     */
    public function editComment(int $commentId, int $customerId, string $body): \CustomFeature\ClassRanker\Models\DiscussionComment
    {
        $comment = $this->model
            ->where('id', $commentId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $comment->update(['body' => $body, 'edited_at' => now()]);

        return $comment->fresh();
    }

    /**
     * Soft delete by customer.
     */
    public function softDeleteByCustomer(int $commentId, int $customerId): void
    {
        $comment = $this->model
            ->where('id', $commentId)
            ->where('customer_id', $customerId)
            ->firstOrFail();

        $comment->update(['deleted_by_type' => 'customer', 'deleted_by_id' => $customerId]);

        $comment->delete();

        Discussion::where('id', $comment->discussion_id)->decrement('comments_count');
    }

    /**
     * Soft delete by admin.
     */
    public function softDeleteByAdmin(int $commentId, int $adminId): void
    {
        $comment = $this->model->withTrashed()->findOrFail($commentId);
        $comment->update(['deleted_by_type' => 'admin', 'deleted_by_id' => $adminId]);
        $comment->delete();

        Discussion::where('id', $comment->discussion_id)->decrement('comments_count');
    }

    /**
     * Toggle comment like. Returns ['liked' => bool, 'count' => int].
     */
    public function toggleLike(int $commentId, int $customerId): array
    {
        $comment  = $this->findOrFail($commentId);
        $existing = DiscussionCommentLike::where('discussion_comment_id', $commentId)
                                          ->where('customer_id', $customerId)
                                          ->first();

        if ($existing) {
            $existing->delete();
            $comment->decrement('likes_count');

            return ['liked' => false, 'count' => $comment->fresh()->likes_count];
        }

        DiscussionCommentLike::create(['discussion_comment_id' => $commentId, 'customer_id' => $customerId]);
        $comment->increment('likes_count');
        
        return ['liked' => true, 'count' => $comment->fresh()->likes_count];
    }

    /**
     * Paginated comments for a discussion (separate endpoint).
     */
    public function paginateForDiscussion(int $discussionId, int $perPage = 15, ?string $search = null)
    {
        $query = $this->model
            ->withTrashed()
            ->with(['customer:id,first_name,last_name,image'])
            ->where('discussion_id', $discussionId);

        if ($search) {
            $query->where('body', 'LIKE', '%' . $search . '%');
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    public function resetCorrectAnswers(int $discussionId): void
    {
        $this->model
            ->where('discussion_id', $discussionId)
            ->update([
                'is_correct' => false,
            ]);
    }

    public function markAsCorrect(int $discussionId, array $commentIds): void
    {
        $this->model
            ->where('discussion_id', $discussionId)
            ->whereIn('id', $commentIds)
            ->update([
                'is_correct' => true,
            ]);
    }
}