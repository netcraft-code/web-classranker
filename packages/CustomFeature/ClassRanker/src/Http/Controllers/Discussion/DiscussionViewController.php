<?php
namespace CustomFeature\ClassRanker\Http\Controllers\Discussion;

use CustomFeature\ClassRanker\Models\Discussion;
use CustomFeature\ClassRanker\Models\DiscussionBlock;
use CustomFeature\ClassRanker\Models\DiscussionComment;
use CustomFeature\ClassRanker\Models\DiscussionLike;
use CustomFeature\ClassRanker\Repositories\DiscussionCommentRepository;
use CustomFeature\ClassRanker\Repositories\DiscussionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;

class DiscussionViewController extends Controller
{
    public function __construct(
        protected DiscussionCommentRepository $commentRepository,
        protected DiscussionRepository $discussionRepository
    ) {}

    // ─── Show page ────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $discussion = Discussion::with(['creator', 'hashtags'])
            ->withCount(['comments', 'likes'])
            ->findOrFail($id);

        return view('class_ranker::discussions.show', compact('discussion'));
    }

    // ─── AJAX: Paginated likes ────────────────────────────────────────────────

    public function ajaxLikes(Request $request, int $id)
    {
        $likes = DiscussionLike::with('customer:id,first_name,last_name,email,image')
            ->where('discussion_id', $id)
            ->latest()
            ->paginate(20);

        // Attach block status per customer
        $blockedIds = DiscussionBlock::pluck('customer_id')->toArray();
        $likes->getCollection()->transform(function ($like) use ($blockedIds) {
            $like->customer->is_blocked = in_array($like->customer_id, $blockedIds);
            return $like;
        });

        return response()->json($likes);
    }

    // ─── AJAX: Paginated comments + search ───────────────────────────────────

    public function ajaxComments(Request $request, int $id)
    {
        $blockedIds = DiscussionBlock::pluck('customer_id')->toArray();

        $comments = $this->commentRepository->paginateForDiscussion(
            $id,
            15,
            $request->get('search')
        );

        $comments->getCollection()->transform(function ($c) use ($blockedIds) {
            if ($c->customer) {
                $c->customer->is_blocked = in_array($c->customer_id, $blockedIds);
            }
            return $c;
        });

        return response()->json($comments);
    }

    // ─── AJAX: Update comment ─────────────────────────────────────────────────

    public function updateComment(Request $request, int $id, int $commentId)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $comment = DiscussionComment::withTrashed()
            ->where('discussion_id', $id)
            ->findOrFail($commentId);

        $comment->update(['body' => $request->body, 'edited_at' => now()]);

        return response()->json(['message' => 'Comment updated.', 'data' => $comment->fresh()]);
    }

    // ─── AJAX: Soft delete comment ────────────────────────────────────────────

    public function deleteComment(int $id, int $commentId)
    {
        $this->commentRepository->softDeleteByAdmin(
            $commentId,
            auth()->guard('admin')->id()
        );

        return response()->json(['message' => 'Comment deleted.']);
    }

    // ─── AJAX: Block customer ─────────────────────────────────────────────────

    public function blockCustomer(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'reason'      => 'nullable|string|max:255',
        ]);

        DiscussionBlock::firstOrCreate(
            ['customer_id' => $request->customer_id],
            ['blocked_by_id' => auth()->guard('admin')->id(), 'reason' => $request->reason ?? '']
        );

        return response()->json(['message' => 'Customer blocked from all discussions.']);
    }

    // ─── AJAX: Unblock customer ───────────────────────────────────────────────

    public function unblockCustomer(int $customerId)
    {
        DiscussionBlock::where('customer_id', $customerId)->delete();
        return response()->json(['message' => 'Customer unblocked.']);
    }

    // Toggle correct answer
    public function markCorrectAnswers(Request $request, int $discussionId)
    {
        $validated = $request->validate([
            'comment_ids'   => 'required|array|min:1',
            'comment_ids.*' => 'integer',
        ]);

        $discussion = $this->discussionRepository->findOrFail($discussionId);

        DB::transaction(function () use ($discussionId, $discussion, $validated) {
            // Reset previous correct answers
            $this->commentRepository->resetCorrectAnswers($discussionId);

            // Mark selected comments as correct
            $this->commentRepository->markAsCorrect(
                $discussionId,
                $validated['comment_ids']
            );

            // Mark discussion resolved
            $this->discussionRepository->update([
                'is_resolved' => true,
            ], $discussion->id);
        });

        return response()->json([
            'message'     => 'Correct answers updated successfully.',
            'is_resolved' => true,
        ]);
    }
}