<?php

namespace CustomFeature\Pdf\Models;

use CustomFeature\Pdf\Contracts\PdfItem as PdfItemContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PdfItem extends Model implements PdfItemContract
{
    protected $fillable = [
        'pdf_id', 'title', 'pdf_path', 'position', 'status',
    ];

    protected $casts = [
        'status'   => 'boolean',
        'position' => 'integer',
    ];

    public function getPdfPathUrlAttribute()
    {
        if ($this->pdf_path) {
            return Storage::disk('public')->url($this->pdf_path);
        }

        return null;
    }

    public function pdf(): BelongsTo
    {
        return $this->belongsTo(Pdf::class);
    }
}