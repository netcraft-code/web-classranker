<?php

namespace CustomFeature\Pdf\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Eloquent\Repository;
use CustomFeature\Pdf\Contracts\Pdf;

class PdfRepository extends Repository
{
    public function model(): string
    {
        return Pdf::class;
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $pdf = $this->model->create([
                'title'       => $data['title'],
                'short_title' => $data['short_title'],
                'slug'        => $data['slug'],
            ]);

            // Assignments
            foreach ($data['assignments'] ?? [] as $assignment) {
                $pdf->assignments()->create([
                    'board_id'   => $assignment['board_id'],
                    'grade_id'   => $assignment['grade_id'],
                    'subject_id' => $assignment['subject_id'],
                    'book_id'    => $assignment['book_id'],
                    'chapter_id' => $assignment['chapter_id'],
                ]);
            }

            DB::commit();
            return $pdf;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();
        
        try {
            $pdf = $this->findOrFail($id);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $pdf->update([
                'title'              => $data['title'],
                'short_title'        => $data['short_title'],
                'slug'               => $data['slug'],
                'top_description'    => $data['top_description']    ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'meta_title'         => $data['meta_title']         ?? null,
                'meta_description'   => $data['meta_description']   ?? null,
                'meta_keywords'      => $data['meta_keywords']      ?? null,
                'status'             => $data['status']             ?? 0,
                'is_premium'         => $data['is_premium']         ?? 0,
            ]);

            DB::commit();

            return $pdf;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $pdf = $this->findOrFail($id);

            foreach ($pdf->pdfItems as $item) {
                Storage::disk('public')->delete($item->pdf_path);
            }

            $pdf->assignments()->delete();

            $pdf->pdfItems()->delete();

            $pdf->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getByChapter(int $chapterId, int $limit = 3, int $page = 1): array
    {
        $query = $this->model
            ->whereHas('assignments', fn($q) =>
                $q->where('chapter_id', $chapterId)
            )
            ->where('status', 1)
            ->withCount('pdfItems');

        $total = $query->count();

        $items = $query
            ->select('id', 'title', 'short_title', 'slug', 'is_premium')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return [
            'data'     => $items,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
            'has_more' => ($page * $limit) < $total,
        ];
    }
}