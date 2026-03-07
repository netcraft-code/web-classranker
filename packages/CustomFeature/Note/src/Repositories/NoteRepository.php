<?php
namespace CustomFeature\Note\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Eloquent\Repository;
use CustomFeature\Note\Contracts\Note;

class NoteRepository extends Repository
{
    public function model(): string
    {
        return Note::class;
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $note = $this->model->create([
                'title'              => $data['title'],
                'short_title'        => $data['short_title'],
                'slug'               => $data['slug'],
                'top_description'    => $data['top_description']    ?? null,
                'content'            => $data['content']            ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'meta_title'         => $data['meta_title']         ?? null,
                'meta_description'   => $data['meta_description']   ?? null,
                'meta_keywords'      => $data['meta_keywords']      ?? null,
                'status'             => $data['status']             ?? 1,
                'is_premium'         => $data['is_premium']         ?? 0,
            ]);

            foreach ($data['assignments'] ?? [] as $assignment) {
                $note->assignments()->create([
                    'board_id'   => $assignment['board_id'],
                    'grade_id'   => $assignment['grade_id'],
                    'subject_id' => $assignment['subject_id'],
                    'book_id'    => $assignment['book_id'],
                    'chapter_id' => $assignment['chapter_id'],
                ]);
            }

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(array $data, $id)
    {
        DB::beginTransaction();
        try {
            $note = $this->findOrFail($id);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            $note->update([
                'title'              => $data['title'],
                'short_title'        => $data['short_title'],
                'slug'               => $data['slug'],
                'top_description'    => $data['top_description']    ?? null,
                'content'            => $data['content']            ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'meta_title'         => $data['meta_title']         ?? null,
                'meta_description'   => $data['meta_description']   ?? null,
                'meta_keywords'      => $data['meta_keywords']      ?? null,
                'status'             => $data['status']             ?? 1,
                'is_premium'         => $data['is_premium']         ?? 0,
            ]);

            // Assignments delete + recreate
            $note->assignments()->delete();
            foreach ($data['assignments'] ?? [] as $assignment) {
                $note->assignments()->create([
                    'board_id'   => $assignment['board_id'],
                    'grade_id'   => $assignment['grade_id'],
                    'subject_id' => $assignment['subject_id'],
                    'book_id'    => $assignment['book_id'],
                    'chapter_id' => $assignment['chapter_id'],
                ]);
            }

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $note = $this->findOrFail($id);
            $note->assignments()->delete();
            $note->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getByChapter(int $chapterId, int $limit = 10, int $page = 1): array
    {
        $query = $this->model
            ->whereHas('assignments', fn($q) =>
                $q->where('chapter_id', $chapterId)
            )
            ->where('status', 1);

        $total = $query->count();
        $items = $query
            ->select('id', 'title', 'short_title', 'slug', 'content')
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