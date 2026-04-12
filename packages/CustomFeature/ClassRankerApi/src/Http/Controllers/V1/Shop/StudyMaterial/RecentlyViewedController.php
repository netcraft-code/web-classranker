<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRanker\Repositories\RecentlyViewedRepository;
use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\Note\Models\Note;
use CustomFeature\Pdf\Models\PdfItem;
use CustomFeature\Question\Models\Question;
use CustomFeature\Quiz\Models\Quiz;
use CustomFeature\Video\Models\Video;
use Illuminate\Http\Request;

class RecentlyViewedController extends StudyMaterialController
{
    public function isAuthorized(): bool
    {
        return true;
    }

    public function repository(): string
    {
        return RecentlyViewedRepository::class;
    }

    private array $modelMap = [
        'question'      => Question::class,
        'note'          => Note::class,
        'pdf_item'      => PdfItem::class,
        'quiz'          => Quiz::class,
        'video'         => Video::class,
    ];

    /**
     * Record a view — upsert so duplicate na bane, sirf viewed_at update ho
     */
    public function record(Request $request)
    {
        $request->validate([
            'viewable_id'   => 'required|integer',
            'viewable_type' => 'required|in:question,note,pdf_item,quiz,video',
        ]);

        $modelClass = $this->modelMap[$request->viewable_type];
        $modelClass::findOrFail($request->viewable_id); // exists check

        $customer = $this->resolveShopUser($request);

        $data = [
            'customer_id'   => $customer->id,
            'grade_id'      => $customer->grade_id,
            'viewable_id'   => $request->viewable_id,
            'viewable_type' => $modelClass,
        ];

        // Exist kare to sirf viewed_at update karo, warna create karo
        $this->getRepositoryInstance()->updateOrCreate($data, [
            'viewed_at' => now(),
        ]);

        // Customer ke liye max 50 recent items rakhne hain — purane delete karo
        $ids = $this->getRepositoryInstance()
            ->where('customer_id', $customer->id)
            ->orderByDesc('viewed_at')
            ->pluck('id')
            ->skip(50)
            ->values();

        if ($ids->isNotEmpty()) {
            $this->getRepositoryInstance()->whereIn('id', $ids)->delete();
        }

        return response()->json(['message' => 'Recorded']);
    }

    /**
     * Get recently viewed items — paginated, latest first
     */
    public function allResources(Request $request)
    {
        $customer = $this->resolveShopUser($request);

        $query = $this->getRepositoryInstance()
            ->with('viewable')
            ->where('customer_id', $customer->id)
            ->where('grade_id', $customer->grade_id);

        // Filter by type (optional)
        if ($request->filled('viewable_type')) {
            $modelClass = $this->modelMap[$request->viewable_type] ?? null;
            if ($modelClass) {
                $query->where('viewable_type', $modelClass);
            }
        }

        $query->orderByDesc('viewed_at');

        $results = $query->paginate($request->input('limit', 10));

        return response()->json($results);
    }
}