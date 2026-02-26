<?php

namespace CustomFeature\Video\Models;

use CustomFeature\Video\Contracts\VideoItem as VideoItemContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VideoItem extends Model implements VideoItemContract
{
    protected $fillable = [
        'video_id',
        'title',
        'video_link',
        'thumbnail',
        'duration',
        'position',
        'status',
    ];

    protected $casts = [
        'status'   => 'boolean',
        'position' => 'integer',
    ];

    public function getVideoLinkUrlAttribute()
    {
        if ($this->video_link) {
            return Storage::url($this->video_link);
        }

        return null;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->thumbnail) {
            return Storage::url($this->thumbnail);
        }

        return null;
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}