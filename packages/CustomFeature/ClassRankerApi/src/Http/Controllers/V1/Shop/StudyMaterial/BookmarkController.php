<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRanker\Repositories\BookmarkRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\Note\Models\Note;
use CustomFeature\Pdf\Models\PdfItem;
use CustomFeature\Question\Models\QuestionItem;
use CustomFeature\Quiz\Models\QuizQuestion;
use Illuminate\Http\Request;

class BookmarkController extends StudyMaterialController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return true;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return BookmarkRepository::class;
    }
    
    public function toggle(Request $request)
    {
        $request->validate([
            'bookmarkable_id'   => 'required|integer',
            'bookmarkable_type' => 'required|in:question_item,note,pdf_item,quiz_question',
        ]);

        // Type to Model map
        $modelMap = [
            'question_item' => QuestionItem::class,
            'note'          => Note::class,
            'pdf_item'      => PdfItem::class,
            'quiz_question' => QuizQuestion::class,
        ];

        $modelClass = $modelMap[$request->bookmarkable_type];
        $model      = $modelClass::findOrFail($request->bookmarkable_id);

        $customer = $this->resolveShopUser($request);

        $data = [
            'customer_id'       => $customer->id,
            'grade_id'          => $customer->grade_id,
            'bookmarkable_id'   => $request->bookmarkable_id,
            'bookmarkable_type' => $modelClass,
        ];

        $existing = $this->getRepositoryInstance()->where($data)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false, 'message' => 'Bookmark removed']);
        }

        $this->getRepositoryInstance()->create($data);

        return response()->json(['bookmarked' => true, 'message' => 'Bookmark added']);
    }

    public function allResources(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request, $customer) {
            
            if ($this->isAuthorized()) {
                $query = $query->where('customer_id', $customer->id)
                            ->where('grade_id', $customer->grade_id);
            }

            if ($request->input('bookmarkable_type')) {
                $query = $query->where('bookmarkable_type', $request->input('bookmarkable_type'));
            }

            if ($sort = $request->input('sort')) {
                $query = $query->orderBy($sort, $request->input('order') ?? 'desc');
            } else {
                $query = $query->latest();
            }

            return $query;
        });

        if ($request->input('pagination') !== 'false' && $request->input('pagination') !== false) {
            $results = $query->with('bookmarkable')
                            ->paginate($request->input('limit') ?? 10);
        } else {
            $results = $query->with('bookmarkable')
                            ->get()
                            ->groupBy('bookmarkable_type');
        }

        return response()->json($results);
    }
}
