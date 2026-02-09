<?php

namespace Webkul\ClassRanker\Repositories;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Storage;
use Webkul\ClassRanker\Contracts\Question;
use Illuminate\Support\Facades\DB;

class QuestionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Question::class;
    }
    
    /**
     * Create question with all related data
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Create main question
            $question = $this->model->create([
                'title' => $data['title'],
                'short_title' => $data['short_title'],
                'slug' => $data['slug'],
                'top_description' => $data['top_description'] ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'related_links' => $data['related_links'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'status' => $data['status'] ?? 1,
                'is_premium' => $data['is_premium'] ?? 0,
            ]);

            // Create assignments
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignment) {
                    $question->assignments()->create([
                        'board_id' => $assignment['board_id'],
                        'grade_id' => $assignment['grade_id'],
                        'subject_id' => $assignment['subject_id'],
                        'book_id' => $assignment['book_id'],
                        'chapter_id' => $assignment['chapter_id'],
                    ]);
                }
            }

            // Create question items
            if (!empty($data['question_items'])) {
                foreach ($data['question_items'] as $index => $item) {
                    $question->questionItems()->create([
                        'question_number' => $item['question_number'],
                        'question' => $item['question'],
                        'answer' => $item['answer'],
                        'page_number' => $item['page_number'] ?? null,
                        'order' => $item['order'] ?? $index,
                    ]);
                }
            }

            // Create FAQs
            if (!empty($data['faqs'])) {
                foreach ($data['faqs'] as $index => $faq) {
                    $question->faqs()->create([
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                        'order' => $faq['order'] ?? $index,
                    ]);
                }
            }

            DB::commit();

            return $question;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Update question with all related data
     */
    public function update(array $data, $id)
    {
        DB::beginTransaction();

        try {
            $question = $this->findOrFail($id);

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['title']);
            }

            // Update main question
            $question->update([
                'title' => $data['title'],
                'short_title' => $data['short_title'],
                'slug' => $data['slug'],
                'top_description' => $data['top_description'] ?? null,
                'bottom_description' => $data['bottom_description'] ?? null,
                'related_links' => $data['related_links'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'status' => $data['status'] ?? 1,
                'is_premium' => $data['is_premium'] ?? 0,
            ]);

            // Update assignments (delete old and create new)
            $question->assignments()->delete();
            if (!empty($data['assignments'])) {
                foreach ($data['assignments'] as $assignment) {
                    $question->assignments()->create([
                        'board_id' => $assignment['board_id'],
                        'grade_id' => $assignment['grade_id'],
                        'subject_id' => $assignment['subject_id'],
                        'book_id' => $assignment['book_id'],
                        'chapter_id' => $assignment['chapter_id'],
                    ]);
                }
            }

            // Update question items (delete old and create new)
            $question->questionItems()->delete();
            if (!empty($data['question_items'])) {
                foreach ($data['question_items'] as $index => $item) {
                    $question->questionItems()->create([
                        'question_number' => $item['question_number'],
                        'question' => $item['question'],
                        'answer' => $item['answer'],
                        'page_number' => $item['page_number'] ?? null,
                        'order' => $item['order'] ?? $index,
                    ]);
                }
            }

            // Update FAQs (delete old and create new)
            $question->faqs()->delete();
            if (!empty($data['faqs'])) {
                foreach ($data['faqs'] as $index => $faq) {
                    $question->faqs()->create([
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                        'order' => $faq['order'] ?? $index,
                    ]);
                }
            }

            DB::commit();

            return $question;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Delete question and all related data
     */
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $question = $this->findOrFail($id);

            // Delete all related data (cascade will handle it, but being explicit)
            $question->assignments()->delete();
            $question->questionItems()->delete();
            $question->faqs()->delete();

            // Delete main question
            $question->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
