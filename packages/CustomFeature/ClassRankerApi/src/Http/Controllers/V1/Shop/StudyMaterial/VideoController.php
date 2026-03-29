<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\VideoResource;
use CustomFeature\Video\Repositories\VideoRepository;
use Illuminate\Http\Request;

class VideoController extends StudyMaterialController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return VideoRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return VideoResource::class;
    }

    public function allResources(Request $request)
    {
        $gradeId   = $request->input('grade_id');
        $chapterId = $request->input('chapter_id');

        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request, $gradeId, $chapterId) {

            // Chapter filter — position sort, dono status true
            if ($chapterId) {
                $query = $query
                    ->join('video_assignments', 'videos.id', '=', 'video_assignments.video_id')
                    ->join('video_items', 'videos.id', '=', 'video_items.video_id')
                    ->where('videos.status', true)
                    ->where('video_items.status', true)
                    ->where('video_assignments.chapter_id', $chapterId)
                    ->select(
                        'videos.*',
                        'video_items.id as item_id',
                        'video_items.title as item_title',
                        'video_items.video_link',
                        'video_items.thumbnail',
                        'video_items.duration',
                        'video_items.position',
                    )
                    ->orderBy('video_items.position', 'asc');

                return $query;
            }

            // Grade filter — random pagination
            if ($gradeId) {
                $query = $query
                    ->join('video_assignments', 'videos.id', '=', 'video_assignments.video_id')
                    ->join('video_items', 'videos.id', '=', 'video_items.video_id')
                    ->where('videos.status', true)
                    ->where('video_items.status', true)
                    ->where('video_assignments.grade_id', $gradeId)
                    ->select(
                        'videos.*',
                        'video_items.id as item_id',
                        'video_items.title as item_title',
                        'video_items.video_link',
                        'video_items.thumbnail',
                        'video_items.duration',
                        'video_items.position',
                    )
                    ->inRandomOrder();

                return $query;
            }

            return $query->where('videos.status', true);
        });

        // Pagination
        if (is_null($request->input('pagination')) || $request->input('pagination')) {
            $results = $query->paginate($request->input('limit') ?? 10);
        } else {
            $results = $query->get();
        }

        return $this->getResourceCollection($results);
    }

    public function subjectChapterList(Request $request)
    {
        $gradeId = $request->query('grade_id');

        if (!$gradeId) {
            return response()->json(['message' => 'grade_id required'], 422);
        }

        // Subjects jo is grade ke chapters mein hain
        $subjects = $this->getRepositoryInstance()->whereHas('chapters', function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId);
            })
            ->with(['chapters' => function ($q) use ($gradeId) {
                $q->where('grade_id', $gradeId)
                ->withCount(['shortVideos']) // short_videos table se count
                ->orderBy('chapter_number');
            }])
            ->get()
            ->map(fn($subject) => [
                'id'       => $subject->id,
                'name'     => $subject->name,
                'icon'     => $subject->icon ?? null,
                'color'    => $subject->color ?? null,
                'chapters' => $subject->chapters->map(fn($ch) => [
                    'id'             => $ch->id,
                    'name'           => $ch->name,
                    'chapter_number' => $ch->chapter_number,
                    'video_count'    => $ch->short_videos_count,
                ]),
            ]);

        return response()->json(['data' => $subjects]);
    }
}
