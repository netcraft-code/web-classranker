<?php

namespace CustomFeature\ClassRanker\Http\Controllers\StudyMaterial;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use CustomFeature\ClassRanker\DataGrids\StudyMaterial\QuizDataGrid;
use CustomFeature\ClassRanker\Http\Requests\StoreQuizRequest;
use CustomFeature\ClassRanker\Http\Requests\UpdateQuizRequest;
use CustomFeature\ClassRanker\Jobs\ProcessQuizBulkUpload;
use CustomFeature\ClassRanker\Models\QuizBulkUpload;
use CustomFeature\ClassRanker\Repositories\BoardRepository;
use CustomFeature\ClassRanker\Repositories\QuizRepository;
use CustomFeature\ClassRanker\Services\CsvQuizParserService;
use CustomFeature\ClassRanker\Services\PdfQuizParserService;

class QuizController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected QuizRepository $quizRepository,
        protected BoardRepository $boardRepository
    ) {}
    
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(QuizDataGrid::class)->process();
        }

        return view('class_ranker::study-material.quizzes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        return view('class_ranker::study-material.quizzes.create', compact('boards'));
    }

    /**
     * Store a newly created quiz in storage
     */
    public function store(StoreQuizRequest $request): RedirectResponse
    {
        try {
            $this->quizRepository->create($request->validated());

            return redirect()
                ->route('admin.study_materials.quizzes.index')
                ->with('success', 'Quiz created successfully!');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error creating quiz: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified quiz
     */
    public function show(int $id): View
    {
        $quiz = $this->quizService->getQuizById($id);

        if (!$quiz) {
            abort(404, 'Quiz not found');
        }

        return view('class_ranker::study-material.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified quiz
     */
    public function edit(int $id): View
    {
        $quiz = $this->quizRepository->getWithRelations($id);

        if (!$quiz) {
            abort(404, 'Quiz not found');
        }

        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();

        // 🔥 PROPERLY FORMAT DATA (like Code 1)
        $formattedChapters = $quiz->quizChapters->map(function($qc) {
            return [
                'boardId' => (string) $qc->board_id,
                'gradeId' => (string) $qc->grade_id,
                'subjectId' => (string) $qc->subject_id,
                'bookId' => (string) $qc->book_id,
                'chapterId' => (string) $qc->chapter_id,
                'boardName' => $qc->board->name,
                'gradeName' => $qc->grade->name,
                'subjectName' => $qc->subject->name,
                'bookName' => $qc->book->title,
                'chapterName' => $qc->chapter->title,
            ];
        })->toArray();
        
        $formattedQuestions = $quiz->questions->map(function($q) {
            return [
                'text' => $q->question_text,
                'options' => $q->options->map(function($opt) {
                    return [
                        'text' => $opt->option_text,
                        'useTinymce' => (bool) $opt->use_tinymce,
                    ];
                })->toArray(),
                'correctOptions' => $q->options
                    ->filter(fn($opt) => $opt->is_correct)
                    ->pluck('option_order')
                    ->map(fn($order) => (int) $order)
                    ->values()
                    ->toArray(),
            ];
        })->toArray();

        return view('class_ranker::study-material.quizzes.edit', compact('quiz', 'boards', 'formattedChapters', 'formattedQuestions'));
    }

    /**
     * Update the specified quiz in storage
     */
    public function update(UpdateQuizRequest $request, $id): RedirectResponse
    {
        try {
            $this->quizRepository->update($request->validated(), $id);

            return redirect()
                ->route('admin.study_materials.quizzes.index')
                ->with('success', 'Quiz updated successfully!');

        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error updating quiz: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified quiz from storage
     */
    public function destroy($id)
    {
        try {
            $this->quizRepository->delete($id);

            session()->flash('success', trans('class_ranker::app.study_materials.quizzes.delete-success'));

            return response()->json(['message' => true], 200);
        } catch (\Exception $e) {
            session()->flash('error', trans('class_ranker::app.study_materials.quizzes.delete-error'));

            return response()->json(['message' => false], 500);
        }
    }

    /**
     * Show bulk upload page
     */
    public function bulkUpload()
    {
        $boards = $this->boardRepository->with(['grades.subjects.books.chapters'])->all();
        
        return view('class_ranker::study-material.quizzes.bulk-upload', compact('boards'));
    }

    /**
     * Parse uploaded file
     */
    public function parseFile(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:pdf,csv|max:10240',
                'chapters' => 'required|string',
            ]);

            $file = $request->file('file');
            $chapters = json_decode($request->chapters, true);

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded',
                ], 400);
            }

            // Store file
            $filePath = $file->store('quiz-uploads', 'local');
            $fullPath = Storage::disk('local')->path($filePath);

            // Parse based on file type
            $extension = $file->getClientOriginalExtension();
            
            if ($extension === 'pdf') {
                $parser = app(PdfQuizParserService::class);
            } else {
                $parser = app(CsvQuizParserService::class);
            }

            $questions = $parser->parse($fullPath);

            if (empty($questions)) {
                Storage::disk('local')->delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'No questions found in file',
                ], 400);
            }

            // ✅ FIX: Clean and validate data before saving
            $cleanedQuestions = $this->cleanQuestionsData($questions);

            // Test JSON encoding
            $testJson = json_encode(['questions' => $cleanedQuestions]);
            if ($testJson === false) {
                Storage::disk('local')->delete($filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid characters in parsed data. Please check your PDF file.',
                ], 400);
            }

            // Store preview data
            $bulkUpload = QuizBulkUpload::create([
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'file_type' => $extension,
                'status' => 'pending',
                'total_questions' => count($cleanedQuestions),
                'chapters' => $chapters,
                'parsed_data' => ['questions' => $cleanedQuestions],
                'uploaded_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'upload_id' => $bulkUpload->id,
                    'questions' => $cleanedQuestions,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Quiz file parse failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName() ?? 'unknown',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error parsing file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clean questions data recursively
     */
    private function cleanQuestionsData(array $questions): array
    {
        return array_map(function($question) {
            return [
                'text' => $this->sanitizeString($question['text'] ?? ''),
                'options' => array_map(function($option) {
                    return [
                        'text' => $this->sanitizeString($option['text'] ?? ''),
                        'useTinymce' => $option['useTinymce'] ?? false,
                    ];
                }, $question['options'] ?? []),
                'correctOptions' => $question['correctOptions'] ?? [],
                'explanation' => $this->sanitizeString($question['explanation'] ?? ''),
            ];
        }, $questions);
    }

    /**
     * Sanitize string for JSON encoding
     */
    private function sanitizeString(string $text): string
    {
        // Remove null bytes
        $text = str_replace("\0", '', $text);
        
        // Convert to UTF-8
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // Remove invalid UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        
        // Remove control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        return trim($text);
    }

    /**
     * Process bulk upload (dispatch job)
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'upload_id' => 'required|exists:quiz_bulk_uploads,id',
        ]);

        try {
            $bulkUpload = QuizBulkUpload::findOrFail($request->upload_id);

            // Dispatch job to queue
            ProcessQuizBulkUpload::dispatch($bulkUpload);

            return response()->json([
                'success' => true,
                'message' => 'Quiz upload queued for processing',
                'upload_id' => $bulkUpload->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check upload progress
     */
    public function uploadProgress($id)
    {
        $bulkUpload = QuizBulkUpload::findOrFail($id);

        return response()->json([
            'status' => $bulkUpload->status,
            'progress' => $bulkUpload->progress_percentage,
            'processed' => $bulkUpload->processed_questions,
            'total' => $bulkUpload->total_questions,
            'failed' => $bulkUpload->failed_questions,
            'errors' => $bulkUpload->errors,
        ]);
    }
}
