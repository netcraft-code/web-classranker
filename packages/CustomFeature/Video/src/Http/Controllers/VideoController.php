<?php

namespace CustomFeature\Video\Http\Controllers;

use CustomFeature\Board\Repositories\BoardRepository;
use CustomFeature\Video\DataGrids\VideoDataGrid;
use CustomFeature\Video\Repositories\VideoRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Controllers\Controller;

class VideoController extends Controller
{
    public function __construct(
        protected VideoRepository $videoRepository,
        protected BoardRepository $boardRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(VideoDataGrid::class)->process();
        }

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.videos.index', compact('boards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'short_title' => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:videos,slug',

            'assignments'              => 'required|array|min:1',
            'assignments.*.board_id'   => 'required|exists:boards,id',
            'assignments.*.grade_id'   => 'required|exists:grades,id',
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.book_id'    => 'required|exists:books,id',
            'assignments.*.chapter_id' => 'required|exists:chapters,id',
        ]);

        $video = $this->videoRepository->create($request->all());

        return response()->json([
            'message'      => 'Video created successfully.',
            'redirect_url' => route('admin.study_materials.videos.edit', $video->id),
        ]);
    }

    public function edit($id)
    {
        $video = $this->videoRepository->with([
            'assignments.board',
            'assignments.grade',
            'assignments.subject',
            'assignments.book',
            'assignments.chapter',
            'videoItems',
        ])->findOrFail($id);

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        $formattedAssignments = $video->assignments->map(fn($a) => [
            'id'          => $a->id,
            'boardId'     => $a->board_id,
            'gradeId'     => $a->grade_id,
            'subjectId'   => $a->subject_id,
            'bookId'      => $a->book_id,
            'chapterId'   => $a->chapter_id,
            'boardName'   => $a->board->name,
            'gradeName'   => $a->grade->name,
            'subjectName' => $a->subject->name,
            'bookName'    => $a->book->title,
            'chapterName' => $a->chapter->title,
        ]);

        $formattedVideoItems = $video->videoItems->map(fn($item) => [
            'item_id'            => $item->id,
            'title'              => $item->title,
            'video_path'         => $item->video_link,
            'video_url'          => $item->video_link_url,
            'thumbnail_path'     => $item->thumbnail,
            'thumbnail_full_url' => $item->thumbnail_url,
            'duration'           => $item->duration,
            'position'           => $item->position,
            'status'             => $item->status,
            'is_new'             => false,
        ]);

        return view('class_ranker::study-material.videos.edit', compact(
            'video',
            'boards',
            'formattedAssignments',
            'formattedVideoItems'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'short_title' => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:videos,slug,' . $id,

            'top_description'    => 'nullable',
            'bottom_description' => 'nullable',

            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string',

            'status'     => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
        ]);

        $this->videoRepository->update($request->all(), $id);

        session()->flash('success', 'Video updated successfully.');

        return redirect()->route('admin.study_materials.videos.index');
    }

    public function delete($id)
    {
        try {
            $this->videoRepository->delete($id);

            session()->flash('success', 'Video deleted successfully.');

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete video.');

            return response()->json(['message' => false], 500);
        }
    }

    // ── ASSIGNMENTS ────────────────────────────────────────────────
    public function addAssignment(Request $request, $id)
    {
        $request->validate([
            'board_id'   => 'required|exists:boards,id',
            'grade_id'   => 'required|exists:grades,id',
            'subject_id' => 'required|exists:subjects,id',
            'book_id'    => 'required|exists:books,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $video = $this->videoRepository->findOrFail($id);

        $assignment = $video->assignments()->create($request->only([
            'board_id',
            'grade_id',
            'subject_id',
            'book_id',
            'chapter_id',
        ]));

        $assignment->load(['board', 'grade', 'subject', 'book', 'chapter']);

        return response()->json([
            'message'    => 'Assignment added.',
            'assignment' => [
                'id'          => $assignment->id,
                'boardId'     => $assignment->board_id,
                'gradeId'     => $assignment->grade_id,
                'subjectId'   => $assignment->subject_id,
                'bookId'      => $assignment->book_id,
                'chapterId'   => $assignment->chapter_id,
                'boardName'   => $assignment->board->name,
                'gradeName'   => $assignment->grade->name,
                'subjectName' => $assignment->subject->name,
                'bookName'    => $assignment->book->title,
                'chapterName' => $assignment->chapter->title,
            ],
        ]);
    }

    public function removeAssignment($id, $assignmentId)
    {
        $video = $this->videoRepository->findOrFail($id);

        $video->assignments()->findOrFail($assignmentId)->delete();

        return response()->json([
            'message' => 'Assignment removed.'
        ]);
    }

    // ── VIDEO ITEMS ────────────────────────────────────────────────
    public function addVideoItem(Request $request, $id)
    {
        $request->validate([
            'title'              => 'required|string|max:255',
            'video_temp_path'    => 'required|string',
            'thumbnail_temp_path'=> 'nullable|string',
            'duration'           => 'nullable|string|max:20',
            'position'           => 'nullable|integer',
            'status'             => 'nullable|boolean',
        ]);

        $video = $this->videoRepository->findOrFail($id);

        // Move temp files
        $videoPath     = null;
        $thumbnailPath = null;

        if ($request->video_temp_path) {
            $newPath = str_replace('temp/videos', "videos/files/{$id}", $request->video_temp_path);
            Storage::disk('public')->move($request->video_temp_path, $newPath);
            $videoPath = $newPath;
        }

        if ($request->thumbnail_temp_path) {
            $newPath = str_replace('temp/thumbnails', "videos/thumbnails/{$id}", $request->thumbnail_temp_path);
            Storage::disk('public')->move($request->thumbnail_temp_path, $newPath);
            $thumbnailPath = $newPath;
        }

        $item = $video->videoItems()->create([
            'title'      => $request->title,
            'video_link' => $videoPath,
            'thumbnail'  => $thumbnailPath,
            'duration'   => $request->duration,
            'position'   => $request->position ?? $video->videoItems()->count(),
            'status'     => $request->boolean('status', false),
        ]);

        return response()->json([
            'message' => 'Video item added.',
            'item'    => [
                'id'                 => $item->id,
                'title'              => $item->title,
                'video_path'         => $item->video_link,
                'video_url'          => $item->video_link_url,
                'thumbnail_path'     => $item->thumbnail,
                'thumbnail_full_url' => $item->thumbnail_url,
                'duration'           => $item->duration,
                'position'           => $item->position,
                'status'             => $item->status,
            ],
        ]);
    }

    public function updateVideoItem(Request $request, $id, $itemId)
    {
        $request->validate([
            'title'               => 'required|string|max:255',
            'duration'            => 'nullable|string|max:20',
            'position'            => 'nullable|integer',
            'status'              => 'nullable|boolean',
            'video_temp_path'     => 'nullable|string',
            'delete_video'        => 'nullable|boolean',
            'thumbnail_temp_path' => 'nullable|string',
            'delete_thumbnail'    => 'nullable|boolean',
        ]);

        $video = $this->videoRepository->findOrFail($id);

        $item  = $video->videoItems()->findOrFail($itemId);

        $videoPath     = $item->video_link;
        $thumbnailPath = $item->thumbnail;

        // Video handle
        if ($request->video_temp_path) {
            $newPath = str_replace('temp/videos', "videos/files/{$id}", $request->video_temp_path);

            Storage::disk('public')->move($request->video_temp_path, $newPath);

            if ($item->video_link) Storage::disk('public')->delete($item->video_link);

            $videoPath = $newPath;
        } elseif ($request->boolean('delete_video')) {
            if ($item->video_link) Storage::disk('public')->delete($item->video_link);

            $videoPath = null;
        }

        // Thumbnail handle
        if ($request->thumbnail_temp_path) {
            $newPath = str_replace('temp/thumbnails', "videos/thumbnails/{$id}", $request->thumbnail_temp_path);

            Storage::disk('public')->move($request->thumbnail_temp_path, $newPath);

            if ($item->thumbnail) Storage::disk('public')->delete($item->thumbnail);

            $thumbnailPath = $newPath;
        } elseif ($request->boolean('delete_thumbnail')) {
            if ($item->thumbnail) Storage::disk('public')->delete($item->thumbnail);

            $thumbnailPath = null;
        }

        $item->update([
            'title'      => $request->title,
            'video_link' => $videoPath,
            'thumbnail'  => $thumbnailPath,
            'duration'   => $request->duration,
            'position'   => $request->position ?? $item->position,
            'status'     => $request->boolean('status', false),
        ]);

        return response()->json([
            'message' => 'Video item updated.',
            'item'    => [
                'id'                 => $item->id,
                'title'              => $item->title,
                'video_path'         => $item->video_link,
                'video_url'          => $item->video_link_url,
                'thumbnail_path'     => $item->thumbnail,
                'thumbnail_full_url' => $item->thumbnail_url,
                'duration'           => $item->duration,
                'position'           => $item->position,
                'status'             => $item->status,
            ],
        ]);
    }

    public function removeVideoItem($id, $itemId)
    {
        $video = $this->videoRepository->findOrFail($id);
        $item  = $video->videoItems()->findOrFail($itemId);

        if ($item->video_link) Storage::disk('public')->delete($item->video_link);

        if ($item->thumbnail)  Storage::disk('public')->delete($item->thumbnail);

        $item->delete();

        return response()->json(['message' => 'Video item removed.']);
    }
}