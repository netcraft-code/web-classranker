<?php

namespace CustomFeature\Quiz\Http\Controllers;

use CustomFeature\Quiz\Models\Quiz;
use CustomFeature\Quiz\Models\QuizImport;
use CustomFeature\Quiz\Models\QuizQuestion;
use CustomFeature\Quiz\Models\QuizOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Webkul\Admin\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class QuizImportController extends Controller
{
    /**
     * Upload XLS file and start importing questions into DB.
     * Streams progress via JSON polling (status endpoint).
     */
    public function upload(Request $request, int $quizId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $quiz = Quiz::findOrFail($quizId);

        $file            = $request->file('file');
        $originalName    = $file->getClientOriginalName();
        $storedName      = uniqid('quiz_import_') . '.' . $file->getClientOriginalExtension();
        $storedPath      = $file->storeAs('quiz-imports', $storedName, 'local');

        // Create import record
        $import = QuizImport::create([
            'quiz_id'           => $quizId,
            'original_filename' => $originalName,
            'stored_filename'   => $storedName,
            'file_path'         => $storedPath,
            'status'            => 'pending',
            'total_rows'        => 0,
            'imported_rows'     => 0,
        ]);

        // Process synchronously (for simplicity; swap with a Job for large files)
        try {
            $this->processImport($import);
        } catch (\Throwable $e) {
            $import->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error('Quiz import failed', ['import_id' => $import->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Import failed: ' . $e->getMessage(),
                'import'  => $import->fresh(),
            ], 422);
        }

        return response()->json([
            'message' => 'File imported successfully.',
            'import'  => $import->fresh(),
        ]);
    }

    /**
     * Get current status/progress of an import.
     */
    public function status(int $quizId, int $importId): JsonResponse
    {
        $import = QuizImport::where('quiz_id', $quizId)->findOrFail($importId);

        return response()->json(['import' => $import]);
    }

    /**
     * List all imports for a quiz.
     */
    public function index(int $quizId): JsonResponse
    {
        $imports = QuizImport::where('quiz_id', $quizId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['imports' => $imports]);
    }

    /**
     * Delete an import record (does NOT remove questions that were already imported).
     */
    public function destroy(int $quizId, int $importId): JsonResponse
    {
        $import = QuizImport::where('quiz_id', $quizId)->findOrFail($importId);

        // Delete stored file
        if (Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $import->delete();

        return response()->json(['message' => 'Import record deleted.']);
    }

    /**
     * Re-upload: replace an existing import record with a new file.
     */
    public function update(Request $request, int $quizId, int $importId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = QuizImport::where('quiz_id', $quizId)->findOrFail($importId);

        // Delete old file
        if (Storage::disk('local')->exists($import->file_path)) {
            Storage::disk('local')->delete($import->file_path);
        }

        $file         = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storedName   = uniqid('quiz_import_') . '.' . $file->getClientOriginalExtension();
        $storedPath   = $file->storeAs('quiz-imports', $storedName, 'local');

        $import->update([
            'original_filename' => $originalName,
            'stored_filename'   => $storedName,
            'file_path'         => $storedPath,
            'status'            => 'pending',
            'total_rows'        => 0,
            'imported_rows'     => 0,
            'error_message'     => null,
        ]);

        try {
            $this->processImport($import);
        } catch (\Throwable $e) {
            $import->update(['status' => 'failed', 'error_message' => $e->getMessage()]);

            return response()->json([
                'message' => 'Re-import failed: ' . $e->getMessage(),
                'import'  => $import->fresh(),
            ], 422);
        }

        return response()->json([
            'message' => 'File re-imported successfully.',
            'import'  => $import->fresh(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function processImport(QuizImport $import): void
    {
        $import->update(['status' => 'processing']);

        $absolutePath = Storage::disk('local')->path($import->file_path);
        $spreadsheet  = IOFactory::load($absolutePath);
        $sheet        = $spreadsheet->getActiveSheet();
        $rows         = $sheet->toArray(null, true, true, true); // assoc with A/B/C…

        // First row = headers
        $headers = array_map('strtolower', array_map('trim', array_shift($rows)));
        // $headers is now like ['a'=>'question','b'=>'option_a', ...]

        // Build column-key map: 'question' => 'A', 'option_a' => 'B', etc.
        $colMap = array_flip($headers); // ['question'=>'A', 'option_a'=>'B', ...]

        $required = ['question', 'option_a', 'option_b', 'correct_option'];
        foreach ($required as $req) {
            if (! array_key_exists($req, $colMap)) {
                throw new \RuntimeException("Required column '{$req}' not found in file.");
            }
        }

        // Detect how many option columns exist (option_a … option_z)
        $optionCols = [];
        foreach (['a','b','c','d','e','f'] as $letter) {
            $key = 'option_' . $letter;
            if (isset($colMap[$key])) {
                $optionCols[$letter] = $colMap[$key]; // e.g. 'a' => 'B'
            }
        }

        $totalRows = count($rows);
        $import->update(['total_rows' => $totalRows]);

        $imported = 0;

        DB::transaction(function () use ($import, $rows, $colMap, $optionCols, &$imported) {
            foreach ($rows as $row) {
                $questionText = trim($row[$colMap['question']] ?? '');
                if (empty($questionText)) continue; // skip blank rows

                $correctLetter = strtoupper(trim($row[$colMap['correct_option']] ?? ''));
                $solution      = isset($colMap['solution']) ? trim($row[$colMap['solution']] ?? '') : '';

                // Get next question order
                $maxOrder = QuizQuestion::where('quiz_id', $import->quiz_id)->max('question_order') ?? 0;

                $question = QuizQuestion::create([
                    'quiz_id'           => $import->quiz_id,
                    'question_text'     => $questionText,
                    'question_solution' => $solution,
                    'question_order'    => $maxOrder + 1,
                ]);

                // Insert options
                $optOrder = 1;
                foreach ($optionCols as $letter => $colLetter) {
                    $optText = trim($row[$colLetter] ?? '');
                    if ($optText === '') continue;

                    QuizOption::create([
                        'quiz_question_id' => $question->id,
                        'option_text'      => $optText,
                        'is_correct'       => (strtoupper($letter) === $correctLetter),
                        'use_tinymce'      => 0,
                        'option_order'     => $optOrder++,
                    ]);
                }

                $imported++;

                // Update progress every 10 rows
                if ($imported % 10 === 0) {
                    $import->update(['imported_rows' => $imported]);
                }
            }

            $import->update([
                'status'        => 'completed',
                'imported_rows' => $imported,
            ]);
        });
    }

    public function questions(int $quizId): JsonResponse
    {
        $quiz = Quiz::with(['questions.options'])->findOrFail($quizId);
    
        $formatted = $quiz->questions->map(function ($q) {
            return [
                'id'            => $q->id,
                'text'          => $q->question_text,
                'solution'      => $q->question_solution,
                'editing'       => false,
                'saving'        => false,
                'deleting'      => false,
                'correctOptions' => $q->options
                    ->filter(fn($o) => $o->is_correct)
                    ->keys()          // positional index inside the collection
                    ->values()
                    ->toArray(),
                'options' => $q->options->values()->map(fn($o, $idx) => [
                    'id'         => $o->id,
                    'text'       => $o->option_text,
                    'is_correct' => (bool) $o->is_correct,
                    'useTinymce' => (bool) $o->use_tinymce,
                ])->toArray(),
            ];
        })->toArray();
    
        return response()->json(['questions' => $formatted]);
    }
}