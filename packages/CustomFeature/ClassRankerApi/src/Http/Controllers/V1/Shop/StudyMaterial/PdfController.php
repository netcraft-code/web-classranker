<?php

namespace CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial;

use CustomFeature\ClassRankerApi\Http\Controllers\V1\Shop\StudyMaterial\StudyMaterialController;
use CustomFeature\ClassRankerApi\Http\Resources\V1\Shop\StudyMaterial\PdfResource;
use CustomFeature\Pdf\Models\Pdf;
use CustomFeature\Pdf\Models\PdfItem;
use CustomFeature\Pdf\Repositories\PdfRepository;
use Illuminate\Http\Request;

class PdfController extends StudyMaterialController
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
        return PdfRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return PdfResource::class;
    }

    public function allResources(Request $request)
    {
        $chapterId = $request->input('chapter_id');

        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($request, $chapterId) {

            // Chapter filter — position sort, dono status true
            if ($chapterId) {
                $query = $query
                    ->join('pdf_assignments', 'pdfs.id', '=', 'pdf_assignments.pdf_id')
                    ->join('pdf_items', 'pdfs.id', '=', 'pdf_items.pdf_id')
                    ->where('pdfs.status', true)
                    ->where('pdf_items.status', true)
                    ->where('pdf_assignments.chapter_id', $chapterId)
                    ->select(
                        'pdfs.*',
                        'pdf_items.id as item_id',
                        'pdf_items.title as item_title',
                        'pdf_items.pdf_path',
                        'pdf_items.position',
                        'pdf_items.created_at as item_created_at',
                    )
                    ->orderBy('pdf_items.position', 'asc');

                return $query;
            }

            // Default — sirf status true
            $query = $query
                ->join('pdf_items', 'pdfs.id', '=', 'pdf_items.pdf_id')
                ->where('pdfs.status', true)
                ->where('pdf_items.status', true)
                ->select(
                    'pdfs.*',
                    'pdf_items.id as item_id',
                    'pdf_items.title as item_title',
                    'pdf_items.pdf_path',
                    'pdf_items.position',
                    'pdf_items.created_at as item_created_at',
                )
                ->orderBy('pdf_items.position', 'asc');

            return $query;
        });

        // Pagination
        if (is_null($request->input('pagination')) || $request->input('pagination')) {
            $results = $query->paginate($request->input('limit') ?? 10);
        } else {
            $results = $query->get();
        }

        return $this->getResourceCollection($results);
    }

    public function getResource(Request $request, $id)
    {
        $resourceClassName = $this->resource();
        
        $query = $this->getRepositoryInstance()->scopeQuery(function ($query) use ($id) {
            // ✅ Same join structure as allResources
            $query = $query
                ->join('pdf_items', 'pdfs.id', '=', 'pdf_items.pdf_id')
                ->where('pdfs.status', true)
                ->where('pdf_items.status', true)
                ->where('pdfs.id', $id)
                ->select(
                    'pdfs.*',
                    'pdf_items.id as item_id',
                    'pdf_items.title as item_title',
                    'pdf_items.pdf_path',
                    'pdf_items.position',
                )
                ->orderBy('pdf_items.position', 'asc');

            return $query;
        });

        if ($request->has('item_id')) {
            $query = $query->where('pdf_items.id', $request->input('item_id'));
        }

        if ($this->isAuthorized()) {
            $query = $query->where('customer_id', $this->resolveShopUser($request)->id);
        }

        // ✅ Get single resource
        $resource = $query->firstOrFail();

        // ✅ Return as single resource, not collection
        return new $resourceClassName($resource);
    }
}
