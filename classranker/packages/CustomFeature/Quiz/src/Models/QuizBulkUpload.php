<?php

namespace CustomFeature\Quiz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Quiz\Contracts\QuizBulkUpload as QuizBulkUploadContract;
use Webkul\User\Models\Admin;

class QuizBulkUpload extends Model implements QuizBulkUploadContract
{
    protected $fillable = [
        'original_filename',
        'file_path',
        'file_type',
        'status',
        'total_questions',
        'processed_questions',
        'failed_questions',
        'chapters',
        'parsed_data',
        'errors',
        'uploaded_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'chapters' => 'array',
        'parsed_data' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_questions === 0) {
            return 0;
        }
        
        return (int) (($this->processed_questions / $this->total_questions) * 100);
    }
}
