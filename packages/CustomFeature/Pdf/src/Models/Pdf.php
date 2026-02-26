<?php

namespace CustomFeature\Pdf\Models;

use CustomFeature\Board\Models\Board;
use CustomFeature\Grade\Models\Grade;
use CustomFeature\Subject\Models\Subject;
use CustomFeature\Book\Models\Book;
use CustomFeature\Chapter\Models\Chapter;
use CustomFeature\Pdf\Contracts\Pdf as PdfContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pdf extends Model implements PdfContract
{
    protected $fillable = [
        'title', 'short_title', 'slug',
        'top_description', 'bottom_description',
        'meta_title', 'meta_description', 'meta_keywords',
        'status', 'is_premium',
    ];

    protected $casts = [
        'status'     => 'boolean',
        'is_premium' => 'boolean',
    ];

    public function pdfItems(): HasMany
    {
        return $this->hasMany(PdfItem::class)->orderBy('position');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(PdfAssignment::class);
    }

    public function boards(): BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'pdf_assignments')->distinct();
    }

    public function grades(): BelongsToMany
    {
        return $this->belongsToMany(Grade::class, 'pdf_assignments')->distinct();
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'pdf_assignments')->distinct();
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'pdf_assignments')->distinct();
    }

    public function chapters(): BelongsToMany
    {
        return $this->belongsToMany(Chapter::class, 'pdf_assignments')->distinct();
    }
}