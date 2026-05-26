<?php
namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\Discussion;

use CustomFeature\ClassRanker\Models\DiscussionBlock;
use CustomFeature\ClassRanker\Models\DiscussionLike;
use CustomFeature\ClassRanker\Repositories\DiscussionRepository;
use CustomFeature\ClassRanker\Repositories\HashtagRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\ShopController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\Discussion\DiscussionResource;
use Illuminate\Http\Request;

class DiscussionController extends ShopController
{
    public function __construct(
        protected DiscussionRepository $discussionRepository,
        protected HashtagRepository $hashtagRepository,
    ) {}

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return DiscussionResource::class;
    }

    public function allResources(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $query = $this->discussionRepository
            ->with([
                'creator',
                'hashtags',
                'board:id,name',
                'grade:id,name',
                'subject:id,name',
                'book:id,title',
                'chapter:id,title',
            ])
            ->withCount([
                'comments',
            ])
            ->scopeQuery(function ($query) use ($request) {

                $query = $query->active();

                /*
                |--------------------------------------------------------------------------
                | Dynamic Filters
                |--------------------------------------------------------------------------
                */

                $filters = $request->except([
                    ...$this->requestException,
                    'hashtag',
                    'q',
                    'sort',
                    'order',
                ]);

                foreach ($filters as $input => $value) {
                    $query = $query->whereIn(
                        $input,
                        array_map('trim', explode(',', $value))
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Hashtags
                |--------------------------------------------------------------------------
                */

                if ($hashtag = $request->input('hashtag')) {

                    $hashtags = array_map('trim', explode(',', $hashtag));

                    $query = $query->whereHas('hashtags', function ($q) use ($hashtags) {
                        $q->whereIn('slug', $hashtags);
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Search
                |--------------------------------------------------------------------------
                */

                if ($search = $request->input('q')) {

                    $query = $query->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', '%' . $search . '%')
                            ->orWhere('description', 'LIKE', '%' . $search . '%');
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Sorting
                |--------------------------------------------------------------------------
                */

                if ($sort = $request->input('sort')) {

                    $query = $query->orderBy(
                        $sort,
                        $request->input('order') ?? 'desc'
                    );

                } else {
                    $query = $query->latest();
                }

                return $query;
            });

        if (is_null($request->input('pagination')) || $request->input('pagination')) {

            $results = $query->paginate(
                $request->input('limit') ?? 15
            );

        } else {
            $results = $query->get();
        }

        if ($customerId = $customer?->id) {

            $results->getCollection()->transform(function ($discussion) use ($customerId) {

                $discussion->is_liked = $discussion->isLikedBy($customerId);

                return $discussion;
            });
        }

        return $this->getResourceCollection($results);
    }

    // ─── Title hints ──────────────────────────────────────────────────────────

    public function searchTitles(Request $request)
    {
        $q = $request->get('q', '');
        return response()->json(
            strlen($q) >= 2 ? $this->discussionRepository->searchTitles($q) : []
        );
    }

    /**
     * Returns an individual discussion resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getResource(Request $request, $id)
    {
        $resourceClassName = $this->resource();

        $customer = $this->resolveShopUser($request);

        $discussion = $this->discussionRepository
            ->with([
                'creator',
                'hashtags:id,name,slug',
                'board:name',
                'grade:name',
                'subject:name',
                'book:title',
                'chapter:title',
            ])
            ->withCount([
                'comments',
            ])
            ->scopeQuery(function ($query) {

                $query = $query->active();

                return $query;
            })
            ->findOrFail($id);

        // $this->discussionRepository->incrementViews($id);

        $discussion->refresh();

        $discussion->is_liked = $customer
            ? $discussion->isLikedBy($customer->id)
            : false;

        return new $resourceClassName($discussion);
    }

    // ─── Likes list (paginated) ───────────────────────────────────────────────

    public function likes(int $id)
    {
        $this->discussionRepository->findOrFail($id);

        $likes = DiscussionLike::with('customer:id,first_name,last_name,image,is_premium_user')
            ->where('discussion_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json($likes);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'board_id'    => 'required|exists:boards,id',
            'grade_id'    => 'required|exists:grades,id',
            'subject_id'  => 'required|exists:subjects,id',
            'book_id'     => 'required|exists:books,id',
            'chapter_id'  => 'required|exists:chapters,id',
            'hashtag_ids' => 'nullable|array',
            'hashtag_ids.*' => 'exists:hashtags,id',
            'status'      => 'nullable|boolean',
        ]);
        
        $customer = $this->resolveShopUser($request);

        // Block check
        if (DiscussionBlock::where('customer_id', $customer->id)->exists()) {
            return response()->json([
                'message' => 'You have been blocked from discussions.',
                'blocked' => true,
            ], 403);
        }

        $discussion = $this->discussionRepository->createWithHashtags([
            'title'        => $request->title,
            'description'  => $request->description,
            'creator_type' => get_class($customer),
            'creator_id'   => $customer->id,
            'board_id'     => $request->board_id,
            'grade_id'     => $request->grade_id,
            'subject_id'   => $request->subject_id,
            'book_id'      => $request->book_id,
            'chapter_id'   => $request->chapter_id,
            'status'       => true,
        ], $request->input('hashtag_ids', []));

        return response()->json([
            'data'    => $discussion->load('hashtags'),
            'message' => 'Discussion created.',
        ], 201);
    }

    // ─── Toggle Like ──────────────────────────────────────────────────────────

    public function toggleLike(Request $request, int $id)
    {
        $customerId = $this->resolveShopUser($request)->id;

        // Block check
        if (DiscussionBlock::where('customer_id', $customerId)->exists()) {
            return response()->json([
                'message' => 'You have been blocked from discussions.',
                'blocked' => true,
            ], 403);
        }

        $result = $this->discussionRepository->toggleLike($id, $customerId);
        return response()->json($result);
    }

    // ─── Hashtags ─────────────────────────────────────────────────────────────

    public function hashtags(Request $request)
    {
        $hashtags = $this->hashtagRepository
            ->scopeQuery(function ($query) use ($request) {
                $query = $query->active(); // existing active scope

                if ($search = $request->input('q')) {
                    $query = $query->where('name', 'LIKE', '%' . $search . '%');
                }

                return $query->orderBy('name');
            })
            ->all();

        return response()->json(['data' => $hashtags]);
    }
}