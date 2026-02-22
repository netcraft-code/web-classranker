<?php

namespace CustomFeature\ClassRanker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use CustomFeature\ClassRanker\Models\QuizBulkUpload;
use CustomFeature\ClassRanker\Repositories\QuizRepository;

class ProcessQuizBulkUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 3; // Retry 3 times on failure

    protected $bulkUpload;

    public function __construct(QuizBulkUpload $bulkUpload)
    {
        $this->bulkUpload = $bulkUpload;
    }

    public function handle(QuizRepository $quizRepository)
    {
        try {
            // Update status to processing
            $this->bulkUpload->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);

            $parsedData = $this->bulkUpload->parsed_data;
            $chapters = $this->bulkUpload->chapters;
            $errors = [];

            // Process each question
            foreach ($parsedData['questions'] as $index => $questionData) {
                try {
                    DB::beginTransaction();

                    // Create quiz for each question (or group them as needed)
                    $quizData = [
                        'title' => 'Quiz Question ' . ($index + 1) . ' - ' . substr($questionData['text'], 0, 50),
                        'slug' => str()->slug('quiz-' . ($index + 1) . '-' . time()),
                        'description' => $questionData['explanation'] ?? '',
                        'is_active' => true,
                        'chapters' => $chapters,
                        'questions' => [$questionData],
                    ];

                    $quizRepository->create($quizData);

                    DB::commit();

                    // Update progress
                    $this->bulkUpload->increment('processed_questions');
                } catch (\Exception $e) {
                    DB::rollBack();

                    $errors[] = [
                        'question_index' => $index,
                        'error' => $e->getMessage(),
                    ];

                    $this->bulkUpload->increment('failed_questions');

                    Log::error('Quiz bulk upload question failed', [
                        'upload_id' => $this->bulkUpload->id,
                        'question_index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark as completed
            $this->bulkUpload->update([
                'status' => 'completed',
                'completed_at' => now(),
                'errors' => $errors,
            ]);

            // Clean up uploaded file
            if (Storage::disk('local')->exists($this->bulkUpload->file_path)) {
                Storage::disk('local')->delete($this->bulkUpload->file_path);
            }
        } catch (\Exception $e) {
            $this->bulkUpload->update([
                'status' => 'failed',
                'errors' => [['error' => $e->getMessage()]],
            ]);

            Log::error('Quiz bulk upload failed', [
                'upload_id' => $this->bulkUpload->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->bulkUpload->update([
            'status' => 'failed',
            'errors' => [['error' => $exception->getMessage()]],
        ]);
    }
}