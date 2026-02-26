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
                'title'              => $data['title'],
                'short_title'        => $data['short_title'],
                'slug'               => $data['slug'],
                'top_description'    => $data['top_description']    ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'meta_title'         => $data['meta_title']         ?? null,
                'meta_description'   => $data['meta_description']   ?? null,
                'meta_keywords'      => $data['meta_keywords']      ?? null,
                'status'             => $data['status']             ?? 1,
                'is_premium'         => $data['is_premium']         ?? 0,
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

            // PDF Items — temp se permanent move
            foreach ($data['pdf_items'] ?? [] as $index => $item) {
                $pdfPath = null;

                if (!empty($item['pdf_temp_path'])) {
                    $newPath = str_replace('temp/pdfs', 'pdfs/files', $item['pdf_temp_path']);
                    Storage::disk('public')->move($item['pdf_temp_path'], $newPath);
                    $pdfPath = $newPath;
                }

                $pdf->pdfItems()->create([
                    'title'    => $item['title'],
                    'pdf_path' => $pdfPath,
                    'position' => $item['position'] ?? $index,
                    'status'   => $item['status']   ?? 1,
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
                'status'             => $data['status']             ?? 1,
                'is_premium'         => $data['is_premium']         ?? 0,
            ]);

            // Assignments — delete + recreate
            $pdf->assignments()->delete();
            foreach ($data['assignments'] ?? [] as $assignment) {
                $pdf->assignments()->create([
                    'board_id'   => $assignment['board_id'],
                    'grade_id'   => $assignment['grade_id'],
                    'subject_id' => $assignment['subject_id'],
                    'book_id'    => $assignment['book_id'],
                    'chapter_id' => $assignment['chapter_id'],
                ]);
            }

            // PDF Items smart update
            $existingItems    = $pdf->pdfItems()->get()->keyBy('id');
            $submittedItemIds = collect($data['pdf_items'] ?? [])
                ->pluck('item_id')->filter()->toArray();

            // Remove karo jo submit nahi hue
            foreach ($existingItems as $itemId => $existingItem) {
                if (!in_array($itemId, $submittedItemIds)) {
                    Storage::disk('public')->delete($existingItem->pdf_path);
                    $existingItem->delete();
                }
            }

            foreach ($data['pdf_items'] ?? [] as $index => $item) {
                $isNew   = ($item['is_new'] ?? '0') === '1' || empty($item['item_id']);
                $itemId  = $item['item_id'] ?? null;
                $pdfPath = $item['pdf_path'] ?? null;

                if (!empty($item['pdf_temp_path'])) {
                    $newPath = str_replace('temp/pdfs', 'pdfs/files', $item['pdf_temp_path']);
                    Storage::disk('public')->move($item['pdf_temp_path'], $newPath);

                    // Old file delete
                    if (!empty($item['pdf_path'])) {
                        Storage::disk('public')->delete($item['pdf_path']);
                    }
                    $pdfPath = $newPath;

                } elseif (($item['delete_pdf'] ?? '0') === '1') {
                    if (!empty($item['pdf_path'])) {
                        Storage::disk('public')->delete($item['pdf_path']);
                    }
                    $pdfPath = null;
                }

                $itemData = [
                    'title'    => $item['title'],
                    'pdf_path' => $pdfPath,
                    'position' => $item['position'] ?? $index,
                    'status'   => $item['status']   ?? 1,
                ];

                if ($isNew) {
                    $pdf->pdfItems()->create($itemData);
                } else {
                    $existingItems[$itemId]?->update($itemData);
                }
            }

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
}