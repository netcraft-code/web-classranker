<?php

namespace CustomFeature\Quiz\Repositories;

use CustomFeature\Quiz\Contracts\Quiz;
use CustomFeature\Quiz\Models\QuizAnswer;
use CustomFeature\Quiz\Models\QuizAttempt;
use CustomFeature\Quiz\Models\QuizChapter;
use CustomFeature\Quiz\Models\QuizOption;
use CustomFeature\Quiz\Models\QuizQuestion;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            ]);

            // Attach chapters
            if (!empty($data['chapters'])) {
                $this->attachChapters($quiz, $data['chapters']);
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
                'is_premium' => $data['is_premium'] ?? false,
            ]);

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

    public function getByChapter(int $chapterId, int $limit = 3, int $page = 1): array
    {
        $query = $this->model
            ->whereHas('quizChapters', fn($q) =>
                $q->where('chapter_id', $chapterId)
            )
            ->where('status', 1)
            ->withCount('questions');

        $total = $query->count();
        $items = $query
            ->select('id', 'title', 'slug', 'is_premium')
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

    public function submitQuiz($quiz, array $answers, $user): array
    {
        $attempt = QuizAttempt::create([
            'quiz_id'      => $quiz->id,
            'customer_id'  => $user->id,
            'started_at'   => now(),
            'completed_at' => now(),
            'status'       => 'completed',
        ]);

        $allQuestions = QuizQuestion::where('quiz_id', $quiz->id)
            ->with('options')
            ->orderBy('question_order')
            ->get();

        $allQuestionIds = $allQuestions->pluck('id')->toArray();

        $submittedMap = collect($answers)
            ->keyBy('quiz_question_id')
            ->map(fn($a) => $a['quiz_option_id']);

        $correctOptions = QuizOption::whereIn('quiz_question_id', $allQuestionIds)
            ->where('is_correct', true)
            ->pluck('id', 'quiz_question_id');

        $correctCount  = 0;
        $skippedCount  = 0;
        $answersToSave = [];
        $reviewData    = [];

        foreach ($allQuestions as $question) {
            $qid              = $question->id;
            $correctOptionId  = $correctOptions[$qid] ?? null;
            $selectedOptionId = $submittedMap[$qid] ?? null;

            $isSkipped = is_null($selectedOptionId);
            $isCorrect = !$isSkipped && ($selectedOptionId == $correctOptionId);

            if ($isCorrect) $correctCount++;
            if ($isSkipped) $skippedCount++;

            $selectedOption = $isSkipped
                ? null
                : $question->options->firstWhere('id', $selectedOptionId);

            $correctOption = $question->options->firstWhere('id', $correctOptionId);

            $answersToSave[] = [
                'quiz_attempt_id'   => $attempt->id,
                'quiz_question_id'  => $qid,
                'quiz_option_id'    => $selectedOptionId,
                'correct_option_id' => $correctOptionId,
                'is_correct'        => $isCorrect,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            $reviewData[] = [
                'question_id'      => $qid,
                'question_text'    => $question->question_text,
                'question_solution'=> $question->question_solution,
                'is_correct'       => $isCorrect,
                'is_skipped'       => $isSkipped,
                'selected_option'  => $selectedOption ? [
                    'id'          => $selectedOption->id,
                    'option_text' => $selectedOption->option_text,
                ] : null,
                'correct_option'   => $correctOption ? [
                    'id'          => $correctOption->id,
                    'option_text' => $correctOption->option_text,
                ] : null,
            ];
        }

        QuizAnswer::insert($answersToSave);

        $totalQuestions = count($allQuestionIds);
        $wrongCount     = $totalQuestions - $correctCount - $skippedCount;
        $score          = $totalQuestions > 0
            ? round(($correctCount / $totalQuestions) * 100)
            : 0;

        $attempt->update([
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'score'           => $score,
        ]);

        return [
            'attempt_id'      => $attempt->id,
            'quiz_title'      => $quiz->title,
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'wrong_answers'   => $wrongCount,
            'skipped'         => $skippedCount,
            'score'           => $score,
            'answers'         => $reviewData,
        ];
    }
}