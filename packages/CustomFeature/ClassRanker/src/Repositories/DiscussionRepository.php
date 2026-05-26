<?php

namespace CustomFeature\ClassRanker\Repositories;

use CustomFeature\ClassRanker\Contracts\Discussion;
use CustomFeature\ClassRanker\Models\DiscussionLike;
use Webkul\Core\Eloquent\Repository;

class DiscussionRepository extends Repository
{
    public function model(): string
    {
        return Discussion::class;
    }

    /**
     * Create discussion with hashtag sync.
     */
    public function createWithHashtags(array $data, array $hashtagIds = []): \CustomFeature\ClassRanker\Models\Discussion
    {
        $discussion = $this->create($data);

        $discussion->hashtags()->sync($hashtagIds);

        return $discussion;
    }

    /**
     * Update discussion with hashtag sync.
     */
    public function updateWithHashtags(array $data, int $id, array $hashtagIds = []): \CustomFeature\ClassRanker\Models\Discussion
    {
        $this->update($data, $id);

        $discussion = $this->findOrFail($id);

        $discussion->hashtags()->sync($hashtagIds);
        
        return $discussion;
    }

    /**
     * Title hints for autocomplete (returns id + title).
     */
    public function searchTitles(string $query, int $limit = 8): array
    {
        return $this->model
            ->active()
            ->where('title', 'LIKE', '%' . $query . '%')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'title'])
            ->toArray();
    }

    /**
     * Toggle like. Returns ['liked' => bool, 'count' => int].
     */
    public function toggleLike(int $discussionId, int $customerId): array
    {
        $discussion = $this->findOrFail($discussionId);
        $existing   = DiscussionLike::where('discussion_id', $discussionId)
                                     ->where('customer_id', $customerId)
                                     ->first();

        if ($existing) {
            $existing->delete();
            $discussion->decrement('likes_count');
            return ['liked' => false, 'count' => $discussion->fresh()->likes_count];
        }

        DiscussionLike::create(['discussion_id' => $discussionId, 'customer_id' => $customerId]);
        $discussion->increment('likes_count');
        return ['liked' => true, 'count' => $discussion->fresh()->likes_count];
    }

    /**
     * Increment view count.
     */
    public function incrementViews(int $id): void
    {
        $this->model->where('id', $id)->increment('views_count');
    }
}