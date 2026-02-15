<?php

namespace Webkul\ClassRanker\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\ClassRanker\Contracts\Quiz;
use Webkul\ClassRanker\Models\QuizChapter;
use Webkul\ClassRanker\Models\QuizOption;
use Webkul\ClassRanker\Models\QuizQuestion;
use Webkul\Core\Eloquent\Repository;

class QuizRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Quiz::class;
    }

    /**
     * Get all quizzes with pagination
     */
    public function getAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->with(['creator:id,name', 'questions'])
            ->withCount('questions')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all active quizzes
     */
    public function getAllActive(): Collection
    {
        return $this->model
            ->where('status', true)
            ->with(['questions.options'])
            ->latest()
            ->get();
    }

    /**
     * Find quiz by ID
     */
    public function findById(int $id): ?Quiz
    {
        return $this->model->find($id);
    }

    /**
     * Find quiz by slug
     */
    public function findBySlug(string $slug): ?Quiz
    {
        return $this->model
            ->where('slug', $slug)
            ->with(['questions.options'])
            ->first();
    }

    /**
     * Create a new quiz
     */
    public function create(array $data): Quiz
    {
        DB::beginTransaction();

        try {
            // Create quiz
            $quiz = $this->model->create([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? false,
                'created_by' => auth()->id(),
            ]);

            // Attach chapters
            if (!empty($data['chapters'])) {
                $this->attachChapters($quiz, $data['chapters']);
            }

            // Attach questions
            if (!empty($data['questions'])) {
                $this->attachQuestions($quiz, $data['questions']);
            }

            DB::commit();

            return $quiz;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Quiz creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update quiz
     */
    public function update(array $data, $id): bool
    {
        DB::beginTransaction();

        try {
            $quiz = $this->findOrFail($id);

            $quiz->update([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? false,
            ]);

            // Remove old chapters and questions
            $this->detachAllChapters($quiz);
            $this->detachAllQuestions($quiz);

            // Attach new chapters
            if (!empty($data['chapters'])) {
                $this->attachChapters($quiz, $data['chapters']);
            }

            // Attach new questions
            if (!empty($data['questions'])) {
                $this->attachQuestions($quiz, $data['questions']);
            }

            DB::commit();

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Quiz update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Attach chapters to quiz
     */
    public function attachChapters(Quiz $quiz, array $chapters): void
    {
        foreach ($chapters as $chapterData) {
            QuizChapter::create([
                'quiz_id' => $quiz->id,
                'board_id' => $chapterData['board_id'],
                'grade_id' => $chapterData['grade_id'],
                'subject_id' => $chapterData['subject_id'],
                'book_id' => $chapterData['book_id'],
                'chapter_id' => $chapterData['chapter_id'],
            ]);
        }
    }

    /**
     * Attach questions to quiz
     */
    public function attachQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $index => $questionData) {
            // Create question
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $questionData['text'],
                'question_order' => $questionData['order'] ?? $index,
            ]);

            // Create options for this question
            foreach ($questionData['options'] as $optionIndex => $optionData) {
                $isCorrect = in_array($optionIndex, $questionData['correct_options'] ?? []);
                
                QuizOption::create([
                    'quiz_question_id' => $question->id,
                    'option_text' => $optionData['text'],
                    'is_correct' => $isCorrect,
                    'use_tinymce' => $optionData['use_tinymce'] ?? 0,
                    'option_order' => $optionIndex,
                ]);
            }
        }
    }

    /**
     * Remove all chapters from quiz
     */
    public function detachAllChapters(Quiz $quiz): void
    {
        $quiz->quizChapters()->delete();
    }

    /**
     * Remove all questions from quiz
     */
    public function detachAllQuestions(Quiz $quiz): void
    {
        // Delete options first, then questions
        $quiz->questions()->each(function ($question) {
            $question->options()->delete();
            $question->delete();
        });
    }

    /**
     * Get quiz with all relations
     */
    public function getWithRelations(int $id): ?Quiz
    {
        return $this->model
            ->with([
                'creator:id,name',
                'quizChapters.board:id,name',
                'quizChapters.grade:id,name',
                'quizChapters.subject:id,name',
                'quizChapters.book:id,title',
                'quizChapters.chapter:id,title',
                'questions.options',
            ])
            ->find($id);
    }

    /**
     * Check if slug exists
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = $this->model->where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}