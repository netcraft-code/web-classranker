<?php

namespace CustomFeature\Video\Repositories;

use CustomFeature\Video\Contracts\Video;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;

class VideoRepository extends Repository
{
    public function model(): string
    {
        return Video::class;
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $video = $this->model->create([
                'title'       => $data['title'],
                'short_title' => $data['short_title'],
                'slug'        => $data['slug'],
                'status'      => $data['status'] ?? 0,
                'is_premium'  => $data['is_premium'] ?? 0,
            ]);

            // Assignments
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignment) {
                    $video->assignments()->create([
                        'board_id'   => $assignment['board_id'],
                        'grade_id'   => $assignment['grade_id'],
                        'subject_id' => $assignment['subject_id'],
                        'book_id'    => $assignment['book_id'],
                        'chapter_id' => $assignment['chapter_id'],
                    ]);
                }
            }

            DB::commit();

            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $video = $this->findOrFail($id);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $video->update([
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

            // // Existing items ko preserve karo — sirf changes apply karo
            // $existingItems = $video->videoItems()->get()->keyBy('id');

            // // Pehle remove items handle karo — jo form mein nahi aaye
            // $submittedItemIds = collect($data['video_items'] ?? [])
            //     ->pluck('item_id')
            //     ->filter()
            //     ->toArray();

            // // Jo submit nahi hue — delete karo
            // foreach ($existingItems as $id => $existingItem) {
            //     if (!in_array($id, $submittedItemIds)) {
            //         Storage::disk('public')->delete($existingItem->video_url);
            //         Storage::disk('public')->delete($existingItem->thumbnail);
            //         $existingItem->delete();
            //     }
            // }

            // // Ab har submitted item process karo
            // if (!empty($data['video_items'])) {
            //     foreach ($data['video_items'] as $index => $item) {
            //         $isNew    = ($item['is_new'] ?? '0') === '1' || empty($item['item_id']);
            //         $itemId   = $item['item_id'] ?? null;

            //         $videoPath     = $item['video_path']     ?? null;
            //         $thumbnailPath = $item['thumbnail_path'] ?? null;

            //         // ── Video handle ──
            //         if (!empty($item['video_temp_path'])) {
            //             // Naya video upload hua — move karo
            //             $newPath = str_replace('temp/videos', 'videos/files', $item['video_temp_path']);
            //             Storage::disk('public')->move($item['video_temp_path'], $newPath);

            //             // Purana delete karo
            //             if (!empty($item['video_path'])) {
            //                 Storage::disk('public')->delete($item['video_path']);
            //             }
            //             $videoPath = $newPath;

            //         } elseif (($item['delete_video'] ?? '0') === '1') {
            //             // Delete mark kiya — file delete karo, path null
            //             if (!empty($item['video_path'])) {
            //                 Storage::disk('public')->delete($item['video_path']);
            //             }
            //             $videoPath = null;
            //         }

            //         // ── Thumbnail handle ──
            //         if (!empty($item['thumbnail_temp_path'])) {
            //             $newPath = str_replace('temp/thumbnails', 'videos/thumbnails', $item['thumbnail_temp_path']);
            //             Storage::disk('public')->move($item['thumbnail_temp_path'], $newPath);

            //             if (!empty($item['thumbnail_path'])) {
            //                 Storage::disk('public')->delete($item['thumbnail_path']);
            //             }
            //             $thumbnailPath = $newPath;

            //         } elseif (($item['delete_thumbnail'] ?? '0') === '1') {
            //             if (!empty($item['thumbnail_path'])) {
            //                 Storage::disk('public')->delete($item['thumbnail_path']);
            //             }
            //             $thumbnailPath = null;
            //         }

            //         $itemData = [
            //             'title'     => $item['title'],
            //             'video_url' => $videoPath,
            //             'thumbnail' => $thumbnailPath,
            //             'duration'  => $item['duration']  ?? null,
            //             'position'  => $item['position']  ?? $index,
            //             'status'    => $item['status']    ?? 1,
            //         ];

            //         if ($isNew) {
            //             // Naya item — create karo
            //             $video->videoItems()->create($itemData);
            //         } else {
            //             // Existing item — sirf update karo
            //             $existingItems[$itemId]?->update($itemData);
            //         }
            //     }
            // }

            DB::commit();

            return $video;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $video = $this->findOrFail($id);

            $video->assignments()->delete();
            $video->videoItems()->delete();
            $video->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}