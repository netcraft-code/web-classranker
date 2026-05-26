<?php

namespace CustomFeature\ClassRanker\Http\Controllers\Discussion;

use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\ClassRanker\DataGrids\DiscussionDataGrid;
use CustomFeature\ClassRanker\Repositories\DiscussionRepository;
use CustomFeature\ClassRanker\Repositories\HashtagRepository;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;

class DiscussionController extends Controller
{
    public function __construct(
        protected DiscussionRepository $discussionRepository,
        protected HashtagRepository    $hashtagRepository,
        protected BoardRepository      $boardRepository,
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return app(DiscussionDataGrid::class)->toJson();
        }

        return view('class_ranker::discussions.index');
    }

    public function create()
    {
        $hashtags = $this->hashtagRepository->getActive();

        $boards = $this->boardRepository->with([
            'grades.subjects.books.chapters'
        ])->get();

        return view('class_ranker::discussions.create', compact('hashtags', 'boards'));
    }

    /**
     * AJAX: title hints autocomplete.
     */
    public function hints(Request $request)
    {
        $q       = $request->get('q', '');
        $results = strlen($q) >= 2
            ? $this->discussionRepository->searchTitles($q)
            : [];

        return response()->json($results);
    }

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
        
        $admin = auth()->guard('admin')->user();

        $this->discussionRepository->createWithHashtags([
            'title'        => $request->title,
            'description'  => $request->description,
            'creator_type' => get_class($admin),
            'creator_id'   => $admin->id,
            'board_id'     => $request->board_id,
            'grade_id'     => $request->grade_id,
            'subject_id'   => $request->subject_id,
            'book_id'      => $request->book_id,
            'chapter_id'   => $request->chapter_id,
            'status'       => $request->boolean('status', false),
        ], $request->input('hashtag_ids', []));

        session()->flash('success', 'Discussion created successfully.');

        return redirect()->route('admin.discussions.index');
    }

    public function edit(int $id)
    {
        $discussion = $this->discussionRepository->findOrFail($id);

        $discussion->load('hashtags');

        $hashtags   = $this->hashtagRepository->getActive();

        return view('class_ranker::discussions.edit', compact('discussion', 'hashtags'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'hashtag_ids'   => 'nullable|array',
            'hashtag_ids.*' => 'exists:hashtags,id',
            'status'        => 'nullable|boolean',
        ]);

        $this->discussionRepository->updateWithHashtags([
            'title'       => $request->title,
            'description' => $request->description,
            'status'      => $request->boolean('status', false),
        ], $id, $request->input('hashtag_ids', []));

        session()->flash('success', 'Discussion updated successfully.');

        return redirect()->route('admin.discussions.index');
    }

    public function destroy(int $id)
    {
        try {
            $this->discussionRepository->delete($id);

            return response()->json(['message' => 'Discussion deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}