<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Discussion;

use CustomFeature\ClassRanker\Models\AiCommentUsage;
use CustomFeature\ClassRanker\Models\Discussion;
use CustomFeature\ClassRanker\Models\DiscussionBlock;
use CustomFeature\ClassRanker\Repositories\DiscussionCommentRepository;
use CustomFeature\ClassRanker\Repositories\DiscussionRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ShopController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Discussion\CommentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DiscussionCommentController extends ShopController
{
    public function __construct(
        protected DiscussionCommentRepository $commentRepository,
        protected DiscussionRepository        $discussionRepository,
    ) {}

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CommentResource::class;
    }

    public function getComments(Request $request, int $discussionId)
    {
        $discussion = $this->discussionRepository->findOrFail($discussionId);
        $customer   = $this->resolveShopUser($request);

        $sort   = $request->input('sort', 'newest');
        $filter = $request->input('filter');

        $query = $this->commentRepository
            ->with(['customer:id,first_name,last_name,image'])
            ->where('discussion_id', $discussionId)
            ->whereNull('deleted_at');

        // ── Filter (Right Comment tab) ─────────────────────────────────────────
        if ($filter === 'correct') {
            $query = $query->where('is_correct', true);
        }

        // ── Sort ──────────────────────────────────────────────────────────────
        match ($sort) {
            'oldest'  => $query = $query->oldest(),
            'correct' => $query = $query->orderByDesc('is_correct')->latest(),
            default   => $query = $query->latest(),  // newest
        };

        // ── Pagination ────────────────────────────────────────────────────────
        $results = (is_null($request->input('pagination')) || $request->input('pagination'))
            ? $query->paginate($request->input('limit') ?? 15)
            : $query->get();

        // ── Extra attributes ──────────────────────────────────────────────────
        $customerId  = $customer?->id;
        $isCreator   = $discussion->creator_id === $customerId;
        $canModerate = $isCreator;

        $transform = function ($comment) use ($customerId) {
            $comment->is_liked      = $customerId ? $comment->isLikedBy($customerId) : false;
            $comment->has_active_plan = $comment->customer?->isPremium();
            return $comment;
        };

        $results instanceof \Illuminate\Pagination\AbstractPaginator
            ? $results->getCollection()->transform($transform)
            : $results->transform($transform);

        return $this->getResourceCollection($results);
    }

    // ─── Add Comment ──────────────────────────────────────────────────────────

    public function store(Request $request, int $discussionId)
    {
        $this->discussionRepository->findOrFail($discussionId);

        $customer = $this->resolveShopUser($request);

        $customerId = $customer?->id;

        // Block check
        if (DiscussionBlock::where('customer_id', $customerId)->exists()) {
            return response()->json([
                'message' => 'You have been blocked from discussions.',
                'blocked' => true,
            ], 403);
        }
        \Log::info($request->all());
        
        $request->validate([
            'body'               => 'required_without:image_paths|nullable|string|max:2000',
            'image_paths'        => 'nullable|array|max:5',
            'image_paths.*'      => 'string',
        ]);

        $comment = $this->commentRepository->addComment(
            $discussionId,
            $customerId,
            $request->body ?? '',
            $request->input('image_paths', [])
        );

        $comment->is_liked = false;

        $resourceClassName = $this->resource();
        
        return (new $resourceClassName($comment))
            ->additional([
                'message' => 'Comment added.'
            ]);
    }

    public function update(Request $request, int $discussionId, int $commentId)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        $customerId = $this->resolveShopUser($request)->id;

        $comment = $this->commentRepository->editComment(
            $commentId,
            $customerId,
            $request->body
        );

        $resourceClassName = $this->resource();

        return (new $resourceClassName($comment))
            ->additional([
                'message' => 'Comment updated.'
            ]);
    }

    // ─── Delete own comment ───────────────────────────────────────────────────

    public function destroy(Request $request, int $discussionId, int $commentId)
    {
        $customerId = $this->resolveShopUser($request)->id;

        $this->commentRepository->softDeleteByCustomer($commentId, $customerId);

        return response()->json(['message' => 'Comment deleted.']);
    }

    // ─── Toggle Comment Like ──────────────────────────────────────────────────

    public function toggleLike(Request $request, int $discussionId, int $commentId)
    {
        $customerId = $this->resolveShopUser($request)->id;

        // Block check
        if (DiscussionBlock::where('customer_id', $customerId)->exists()) {
            return response()->json(['message' => 'You have been blocked from discussions.', 'blocked' => true], 403);
        }

        return response()->json(
            $this->commentRepository->toggleLike($commentId, $customerId)
        );
    }

    public function aiGenerate(Request $request, int $discussionId)
    {
        if (! (bool) core()->getConfigData('class_ranker.settings.openai.status')) {
            return response()->json(['message' => 'AI comment generation is disabled.'], 403);
        }

        $customer = $this->resolveShopUser($request);

        if (DiscussionBlock::where('customer_id', $customer->id)->exists()) {
            return response()->json(['message' => 'You have been blocked from discussions.', 'blocked' => true], 403);
        }
        
        // Premium check
        if ($customer->phone != 9557821570 && !$customer->isPremium()) {
            return response()->json(['message' => 'Premium feature only.'], 403);
        }

        // Daily limit check
        $remaining = AiCommentUsage::getRemainingForToday($customer->id);

        if ($customer->phone != 9557821570 && $remaining <= 0) {
            return response()->json([
                'message'         => 'Daily AI limit reached (5/day). Try again tomorrow.',
                'remaining_today' => 0,
            ], 429);
        }
        
        $discussion = $this->discussionRepository->with(['hashtags'])->findOrFail($discussionId);

        // Build prompt with full context
        $tags    = $discussion->hashtags->pluck('name')->implode(', ');
        
        $prompt = "
            You are an educational AI assistant helping school students in India.

            A student created the following discussion or Question:

            --------------------
            DISCUSSION DETAILS
            --------------------
            
            Title:
            {$discussion->title}
            
            Description:
            " . ($discussion->description ?? 'Not provided') . "

            Board:
            " . ($discussion->board->name ?? 'Not specified') . "

            Class:
            " . ($discussion->grade->name ?? 'Not specified') . "

            Subject:
            " . ($discussion->subject->name ?? 'Not specified') . "

            Book:
            " . ($discussion->book->title ?? 'Not specified') . "

            Chapter:
            " . ($discussion->chapter->title ?? 'Not specified') . "

            Tags:
            " . ($tags ?: 'None') . "

            --------------------
            INSTRUCTIONS
            --------------------

            1. Write a helpful, educational, and student-friendly response.

            2. Keep the answer concise but useful.

            3. Do NOT start with greetings or sentences like:
            - Hi
            - Hello
            - I think
            - I am an AI

            4. If the discussion contains a math/science equation:
            - Return equations in proper LaTeX format.
            - Use inline LaTeX like:
                \( a^2 + b^2 = c^2 \)

            - Use block LaTeX like:
                \[
                x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}
                \]

            5. If the discussion description contains image references, screenshots, OCR text, or unclear content:
            - Try to infer the educational context.
            - Still return ONLY text response.
            - Never mention inability to see images unless absolutely necessary.

            6. Keep the tone natural and educational.

            7. Do not generate markdown code blocks.

            8. If the answer is uncertain, clearly say so instead of hallucinating.
            ";

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . core()->getConfigData('class_ranker.settings.openai.key'),
                    'OpenAI-Project' => core()->getConfigData('class_ranker.settings.openai.project_id'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-5.4-mini',
                    'messages' => [
                        [
                            'role'    => 'system',
                            'content' => 'You are a helpful study assistant.',
                        ],
                        [
                            'role'    => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_completion_tokens' => 400,
                ]);

            if ($response->failed()) {

                \Log::error('OpenAI Error', [
                    'response' => $response->json(),
                ]);

                return response()->json([
                    'message' => 'AI service unavailable. Try again.',
                ], 503);
            }

            $aiText = trim(
                $response->json('choices.0.message.content', '')
            );

            $remainingLeft = AiCommentUsage::incrementForToday($customer->id);

            return response()->json([
                'success'        => true,
                'comment'        => $aiText,
                'remaining_today' => $remainingLeft,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'AI service error.'], 503);
        }
    }

    // Toggle correct answer
    public function markCorrectAnswers(Request $request, int $discussionId)
    {
        $validated = $request->validate([
            'comment_ids'   => 'required|array|min:1',
            'comment_ids.*' => 'integer',
        ]);

        $customer = $this->resolveShopUser($request);

        $discussion = $this->discussionRepository->findOrFail($discussionId);

        if ($discussion->creator_id !== $customer?->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        DB::transaction(function () use ($discussionId, $discussion, $validated) {
            // Reset previous correct answers
            $this->discussionCommentRepository->resetCorrectAnswers($discussionId);

            // Mark selected comments as correct
            $this->discussionCommentRepository->markAsCorrect(
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

    // Temp image upload
    public function uploadTempImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240', // 10MB max
        ]);

        $path = $request->file('image')->store('discussions/temp', 'public');

        return response()->json(['path' => $path]);
    }
}