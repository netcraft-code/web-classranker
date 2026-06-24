<?php

namespace CustomFeature\Quiz\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Quiz\Contracts\QuizImport as QuizImportContract;

class QuizImport extends Model implements QuizImportContract
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'status',
        'total_rows',
        'imported_rows',
        'error_message',
    ];

    protected $appends = ['progress_percent'];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressPercentAttribute(): int
    {
        if ($this->total_rows <= 0) return 0;
        return (int) round(($this->imported_rows / $this->total_rows) * 100);
    }
}