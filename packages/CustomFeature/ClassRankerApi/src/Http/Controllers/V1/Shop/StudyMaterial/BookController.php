<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\Book\Repositories\BookRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\BookResource;
use Illuminate\Http\Request;

class BookController extends StudyMaterialController
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
        return BookRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return BookResource::class;
    }

    /**
     * Get quiz and video list for a book.
     */
    public function quizVideoList(Request $request)
    {
        $filterType = $request->input('filter_type', 'quiz'); // 'quiz' or 'video'

        $books = $this->getRepositoryInstance()->query()
            ->where('books.status', true)
            // ── standard filters (grade_id, board_id, subject_id etc.) ──
            ->when($request->grade_id,   fn($q) => $q->where('books.grade_id',   $request->grade_id))
            ->when($request->board_id,   fn($q) => $q->where('books.board_id',   $request->board_id))
            ->when($request->subject_id, fn($q) => $q->where('books.subject_id', $request->subject_id))
            ->with(['chapters' => function ($q) use ($filterType) {
                $q->where('chapters.status', true)
                ->withCount([
                    // ── quiz count per chapter ──
                    'quizChapters as quiz_count' => fn($q) =>
                        $q->whereHas('quiz', fn($q) => $q->where('status', true)),

                    // ── video count per chapter ──
                    'videoAssignments as video_count' => fn($q) =>
                        $q->whereHas('video', fn($q) => $q->where('status', true)),
                ])
                // sirf wahi chapters jo selected type mein kuch rakhte hain
                ->when($filterType === 'quiz',  fn($q) => $q->has('quizChapters'))
                ->when($filterType === 'video', fn($q) => $q->has('videoAssignments'))
                ->limit(6);
            }])
            // sirf wahi books jinke chapters hain (filter ke baad)
            ->when($filterType === 'quiz',  fn($q) => $q->whereHas('chapters.quizChapters'))
            ->when($filterType === 'video', fn($q) => $q->whereHas('chapters.videoAssignments'))
            ->orderBy('books.id', 'asc');

        $results = ($request->input('pagination') === 'false')
            ? $books->get()
            : $books->paginate($request->input('limit', 10));
            
        return $this->getResourceCollection($results);
    }
}
