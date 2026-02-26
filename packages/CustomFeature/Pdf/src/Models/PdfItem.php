<?php

namespace CustomFeature\Pdf\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use CustomFeature\Pdf\Contracts\PdfItem as PdfItemContract;

class PdfItem extends Model implements PdfItemContract
{
    protected $fillable = [
        'pdf_id', 'title', 'pdf_path', 'position', 'status',
    ];

    protected $casts = [
        'status'   => 'boolean',
        'position' => 'integer',
    ];

    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Pdf::class);
    }
}